<?php

namespace Alexandria\Controllers;

use Alexandria\Alexandria;
use DateTime;
use Psr\Http\Message\ResponseInterface as Response;

class Upload extends Request {

	public bool $success = true;
	public string $uniqueIdentifier;
	private UploadError $errors;
	public DateTime $creationDatetime;

	function __construct( Response $response, string $inputFile = null ) {
		parent::__construct( $response );
		$this->setUserAction( 'upload' );
		$this->uniqueIdentifier = $this->generateImageIdentifier();

		$this->errors           = new UploadError();
		$this->creationDatetime = new DateTime();

		if ( empty( $inputFile ) ) {
			$this->success = false;

			return $this->writeResponse( $this->errors->get( 'no_file', $this ) );
		}

		if ( ! $this->saveImage( $inputFile ) ) {
			$this->success = false;

			return $this->writeResponse( $this->errors->get( 'failed_to_save', $this ) );
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
			return $this->writeResponse( $this->errors->get( 'dev_failed_to_open', $this ) );
		}

		return imagepng( $image, sprintf( './Storage/Images/%s.png', $this->uniqueIdentifier ) );
	}
}

class UploadError extends RequestError {
	public static array $upload = [
		'no_file'            => [ 'error' => 'no file supplied' ],
		'failed_to_save'     => [ 'error' => 'failed to save file' ],
		'dev_failed_to_open' => [ 'error' => 'failed to open file' ],
	];

	public function get( string $error, Upload $uploadInstance ): array {
		if ( empty( UploadError::$upload[ $error ] ) ) {
			return [];
		}

		if ( strpos( $error, 'dev_' ) !== false && 'production' === Alexandria::$environment ) {
			$json           = json_encode( $uploadInstance );
			$array          = json_decode( $json, true );
			$array['error'] = UploadError::$upload[ $error ]['error'];
			file_put_contents(
				sprintf( './Storage/Errors/%s.json', $uploadInstance->uniqueIdentifier ),
				json_encode( $array )
			);
			$error = new RequestError();

			return $error->request['production_user'];
		}

		return UploadError::$upload[ $error ];
	}
}