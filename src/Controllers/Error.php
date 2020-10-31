<?php

namespace Alexandria\Controllers;

use Alexandria\Alexandria;

class RequestError {

	public static array $request;

	public function __construct() {
		RequestError::$request = [
			'production_user' => $this->format( 'an unexpected error occurred' )
		];
	}

	protected function format( string $error ) {
		return [ 'error' => $error ];
	}

	public function getProductionError(
		string $error,
		Upload $uploadInstance = null,
		Retrieve $retrieveInstance = null
	) {
		if ( is_null( $uploadInstance ) && is_null( $retrieveInstance ) ) {
			new \InvalidArgumentException( 'At least one of $uploadInstance or $retrieveInstance must not be null' );
		}

		$action = 'upload';
		if ( ! is_null( $retrieveInstance ) ) {
			$action = 'retrieve';
		}
		$is_up = 'upload' === $action;

		$json           = $is_up ? json_encode( $uploadInstance ) : json_encode( $retrieveInstance );
		$array          = json_decode( $json, true );
		$array['error'] = $is_up ? UploadError::$upload[ $error ]['error'] : RetrieveError::$retrieve[ $error ]['error'];
		file_put_contents(
			sprintf( './Storage/Errors/%s.json',
				$is_up ? $uploadInstance->uniqueIdentifier : $retrieveInstance->uniqueIdentifier ),
			json_encode( $array )
		);

		return RequestError::$request['production_user'];
	}
}

class UploadError extends RequestError {
	public static array $upload;

	public function __construct() {
		parent::__construct();

		UploadError::$upload = [
			'no_file'            => $this->format('no file supplied'),
			'failed_to_save'     => $this->format('failed to save file'),
			'dev_failed_to_open' => $this->format('failed to open file'),
		];
	}

	public function get( string $error, Upload $uploadInstance ): array {
		if ( empty( UploadError::$upload[ $error ] ) ) {
			return [];
		}

		if ( strpos( $error, 'dev_' ) !== false && 'production' === Alexandria::$environment ) {
			return $this->getProductionError($error, $uploadInstance, null);
		}

		return UploadError::$upload[ $error ];
	}
}

class RetrieveError extends RequestError {
	public static array $retrieve;

	public function __construct() {
		parent::__construct();

		RetrieveError::$retrieve = [
			'empty_identifier'   => $this->format('empty image identifier supplied'),
			'invalid_identifier' => $this->format('invalid image identifier supplied'),
			'image_not_found' => $this->format('an image matching the supplied identifier was not found')
		];
	}

	public function get( string $error, Retrieve $retrieveInstance ): array {
		if ( empty( RetrieveError::$retrieve[ $error ] ) ) {
			return [];
		}

		if ( strpos( $error, 'dev_' ) !== false && 'production' === Alexandria::$environment ) {
			return $this->getProductionError($error, null, $retrieveInstance);
		}

		return RetrieveError::$retrieve[ $error ];
	}
}