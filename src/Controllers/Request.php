<?php

namespace Alexandria\Controllers;

use Psr\Http\Message\ResponseInterface as Response;

interface RequestInterface {
	public function __construct(Response $response, string $inputFile = null);
	public function setUserAction(string $userAction);
	public function getResponse(): Response;
	public function getImageGallery(): array;
}

class Request implements RequestInterface {

	protected array $imageGallery;

	protected Response $response;

	public function __construct(Response $response, string $inputFile = null) {
		$this->imageGallery = array();
		$this->response = $response;
	}

	public function setUserAction(string $userAction) {
		if('retrieve' === $userAction){
			$this->refreshImageGallery();
		}
	}

	public function writeResponse($message): Response {
		$message = json_encode($message);
		$this->response->getBody()->write($message);
		return $this->response;
	}

	public function getResponse(): Response {
		return $this->response;
	}

	protected function refreshImageGallery(): bool {
		// todo: Implement function refreshImageGallery
	}

	public function getImageGallery(): array {
		// TODO: Implement getImageGallery() method.
	}
}