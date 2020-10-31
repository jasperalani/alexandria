<?php

namespace Alexandria\Controllers;

use Alexandria\Alexandria;
use Psr\Http\Message\ResponseInterface as Response;

class Upload extends Request {

	function __construct( Response $response, string $inputFile = null, string $imageIdentifier = "" ) {
		parent::__construct( $response );
		$this->setUserAction( 'upload' );
		$this->uniqueIdentifier = $this->generateImageIdentifier();

		if ( empty( $inputFile ) ) {
			$this->success = false;

			return $this->writeResponse( $this->uploadErrors->get( 'no_file', $this ) );
		}

		if ( ! $this->saveImage( $inputFile ) ) {
			$this->success = false;

			return $this->writeResponse( $this->uploadErrors->get( 'failed_to_save', $this ) );
		}

		if ( $this->success ) {
			return $this->writeResponse( [ 'id' => $this->uniqueIdentifier ] );
		}
	}

	public function generateImageIdentifier(): string {
		return str_replace( '.', '-', sprintf( "%s", uniqid( "", true ) ) );
	}

	function saveImage( $stringData ) {
		$image = imagecreatefromstring( $stringData );
		if ( ! $image ) {
			return $this->writeResponse( $this->uploadErrors->get( 'dev_failed_to_open', $this ) );
		}

		return imagepng( $image, sprintf( './Storage/Images/%s.png', $this->uniqueIdentifier ) );
	}
}