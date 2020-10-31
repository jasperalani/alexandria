<?php

namespace Alexandria\Controllers;

use DateTime;
use Psr\Http\Message\ResponseInterface as Response;

interface RequestInterface {
	public function __construct( Response $response, string $inputFile = null, string $imageIdentifier = "" );

	public function setUserAction( string $userAction );

	public function getResponse(): Response;

	public function getImageGallery(): array;
}

class Request implements RequestInterface {

	public DateTime $creationDatetime;
	public bool $success = true;
	public string $uniqueIdentifier;

	protected array $imageGallery;
	protected Response $response;
	protected RequestError $requestErrors;
	protected UploadError $uploadErrors;
	protected RetrieveError $retrievalErrors;

	public function __construct( Response $response, string $inputFile = null, string $imageIdentifier = "" ) {
		$this->creationDatetime = new DateTime();
		$this->imageGallery     = array();
		$this->response         = $response;

		$this->requestErrors   = new RequestError();
		$this->uploadErrors    = new UploadError();
		$this->retrievalErrors = new RetrieveError();
	}

	public function setUserAction( string $userAction ) {
		if ( 'retrieve' === $userAction && ! empty( $this->imageGallery ) ) {
			$this->refreshImageGallery();
		}
	}

	public function writeResponse( $message ): Response {
		$message = json_encode( $message );
		$this->response->getBody()->write( $message );

		return $this->response;
	}

	public function getResponse(): Response {
		return $this->response;
	}

	protected function refreshImageGallery(): bool {
		$dirSearch = scandir( './Storage/Images' );
		unset( $dirSearch[0], $dirSearch[1] ); // Remove . and ..
		$dirSearch = array_values( $dirSearch );

		if ( empty( $dirSearch ) ) {
			return false;
		}

		foreach ( $dirSearch as $file ) {
			$file = substr( $file, 0, - 4 );
			array_push( $this->imageGallery, $file );
		}

		return true;
	}

	public function getImageGallery(): array {
		return $this->imageGallery;
	}
}