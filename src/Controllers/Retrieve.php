<?php

namespace Alexandria\Controllers;

use Alexandria\Alexandria;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Stream;

class Retrieve extends Request {

	public function __construct( Response $response, string $inputFile = null, string $imageIdentifier = "" ) {
		parent::__construct( $response );
		$this->setUserAction( 'retrieve' );

		if ( empty( $imageIdentifier ) ) {
			$this->success = false;

			return $this->writeResponse( $this->retrievalErrors->get( 'empty_identifier', $this ) );
		}

		if ( ! $this->validateIdentifier( $imageIdentifier ) ) {
			$this->success = false;

			return $this->writeResponse( $this->retrievalErrors->get( 'invalid_identifier', $this ) );
		}
		$this->uniqueIdentifier = $imageIdentifier;

		$this->refreshImageGallery();
		$imageGallery = $this->getImageGallery();

		if ( empty( $imageGallery ) || ! in_array( $imageIdentifier, $imageGallery ) ) {
			$this->success = false;

			return $this->writeResponse( $this->retrievalErrors->get( 'image_not_found', $this ) );
		}
	}

	public function validateIdentifier( string $imageIdentifier ) {
		$validLength            = 23;
		$dashLocation           = 14;
		$regexAllowedCharacters = '([A-Za-z0-9\-]+)';

		if (
			$validLength === strlen( $imageIdentifier )
			&& $dashLocation === strpos( $imageIdentifier, '-' )
			&& preg_match( $regexAllowedCharacters, $imageIdentifier )
		) {
			return true;
		}

		return false;
	}

	public function retrieveImageStream( string $imageIdentifier = '' ) {
		if ( empty( $imageIdentifier ) ) {
			$imageIdentifier = $this->uniqueIdentifier;
		}
		$filePath = "./Storage/Images/$imageIdentifier.png";
		$openFile = fopen( $filePath, 'rb' );

		return new Stream( $openFile );
	}
}
