<?php
namespace WikiParserDemo {

    use Guzzle\Http\Exception\ClientErrorResponseException;
    use Guzzle\Http\Message\Response;

    class Client
    {
        /**
         * try to fetch not found title in x seconds
         */
        const CACHE_404_TIMEOUT = 900;

        /**
         * @var string
         */
        protected $title;
        /**
         * @var \Guzzle\Http\Client
         */
        protected $client;

        /**
         *
         */
        public function __construct()
        {
            $this->initialize();
        }

        /**
         * @param string $title
         * @param Document|null $localDocument
         * @return Document
         */
        public function search($title, Document $localDocument = null)
        {
            $this->title = $title;
            $document = new Document();
            if ($this->updateDocumentOnLastRevIdDiff($document, $localDocument)) {
                $content = $this->downloadDocument();
                if ($content) {
                    $document->update($this->title, $content);
                } else {
                    $document->setNotFoundNow();
                }
            }
            return $document;
        }

        /**
         * @param Document $document
         * @param Document|null $localDocument
         * @return bool
         */
        private function updateDocumentOnLastRevIdDiff(Document &$document, Document $localDocument = null)
        {
            if ($localDocument && $localDocument->getNotFoundWhen() + self::CACHE_404_TIMEOUT > time()) {
                return false;
            }
            $lastRevId = $this->checkLastRevId();
            if ($lastRevId !== false) {
                $document->setLastRevId($lastRevId);
                if (!$localDocument || $localDocument->getLastRevId() !== $document->getLastRevId()) {
                    $document->setDirty();
                    return true;
                }
            }
            return false;
        }

        /**
         *
         */
        protected function initialize()
        {
            $this->client = new \Guzzle\Http\Client();
            $this->client->setUserAgent('WikiParserDemo/1.1 (https://github.com/ibartj/)');
        }

        /**
         * @return bool
         */
        protected function checkLastRevId()
        {
            $request = $this->client->get('https://cs.wikipedia.org/w/api.php?action=query&prop=info&rvprop=revisions&format=json&titles=' . $this->title);
            $response = $request->send();
            if ($response->isSuccessful()) {
                return $this->getFirstPageFromResponse($response)->lastrevid;
            }
            return false;
        }

        /**
         * @return bool
         */
        protected function downloadDocument()
        {
            $request = $this->client->get('https://cs.wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&format=json&titles=' . $this->title);
            $response = $request->send();
            if ($response->isSuccessful()) {
                return $this->getFirstPageFromResponse($response)->revisions[0]->{'*'};
            }
            return false;
        }

        /**
         * @param Response $response
         * @return mixed
         */
        protected function getFirstPageFromResponse(Response $response)
        {
            $jsonData = json_decode($response->getBody(true));
            $vars = array_values(get_object_vars($jsonData->query->pages));
            return $vars[0];
        }
    }
}