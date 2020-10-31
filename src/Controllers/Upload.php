<?php

namespace Alexandria\Controllers;

use Alexandria\Alexandria;
use Psr\Http\Message\ResponseInterface as Response;

class Upload extends Request {

	private string $pathToUploadedFile;

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

		if( Alexandria::$compressionAvailable ) {
			$this->compressImage();
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

		$this->pathToUploadedFile = sprintf( './Storage/Images/%s.png', $this->uniqueIdentifier );
		return imagepng( $image, $this->pathToUploadedFile );
	}

	function compressImage(){
		if( empty($this->pathToUploadedFile) ){
			return false;
		}

		try {
			$compressedImage = compress_png( $this->pathToUploadedFile );
			file_put_contents($this->pathToUploadedFile, $compressedImage);
		} catch ( \Exception $e ) {
			return $this->writeResponse($this->uploadErrors->get('failed_to_compress', $this));
		}

		return true;
	}
}