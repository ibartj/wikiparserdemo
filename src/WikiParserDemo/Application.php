<?php
namespace WikiParserDemo {

    use phpFastCache\CacheManager;

    class Application
    {
        const CACHE_LIFETIME = 3600;
        const DOCUMENT_LIFETIME = 180;
        /**
         * @var \phpFastCache\Core\DriverAbstract
         */
        protected $cache;

        /**
         * @var Client;
         */
        protected $client;

        /**
         * @var Application
         */
        private static $instance;

        /**
         *
         */
        private function __construct()
        {
        }

        /**
         * @param array|null $cacheConfig
         * @return Application
         */
        public static function getInstance(array $cacheConfig = null)
        {
            if (is_null(self::$instance)) {
                self::$instance = new self;
                self::$instance->initialize($cacheConfig);
            }
            return self::$instance;
        }

        /**
         * @param array|null $cacheConfig
         */
        protected function initialize(array $cacheConfig = null)
        {
            if (!$cacheConfig) {
                $cachePath = getcwd() . '/cache';
                if (!file_exists($cachePath)) {
                    mkdir($cachePath, 0777);
                }
                $cacheConfig = array(
                    "storage" => "files",
                    "path" => getcwd() . "/cache",
                );
            }
            $this->cache = CacheManager::getInstance('auto', $cacheConfig);
            $this->client = new Client();
        }

        /**
         * @param bool|true $useCachedResults
         * @return mixed|null|Document
         */
        public function runSearch($useCachedResults = true)
        {
            $searchString = self::getSearchString();
            if (!$searchString) {
                return false;
            }
            $cachedDocument = $useCachedResults ? $this->getCachedDocument($searchString) : null;
            if($cachedDocument && $cachedDocument->getCreatedWhen() + self::DOCUMENT_LIFETIME > time()) {
                return $cachedDocument;
            }
            $result = $this->client->search($searchString, $cachedDocument);
            if ($redirect = $this->checkRedirect($result)) {
                $result = $this->client->search($redirect, $cachedDocument);
            }

            if ($result && $result->isDirty()) {
                $this->cacheDocument($searchString, $result);
                return $result;
            } else {
                return $cachedDocument;
            }
        }

        /**
         * @param Document $document
         * @return bool
         */
        protected function checkRedirect(Document $document)
        {
            if (preg_match('~^[#]redirect \[\[(.+)\]\]$~i', $document->getContent(), $capture)) {
                return $capture[1];
            }
            return false;
        }

        /**
         * @param string $keyword
         * @return mixed|null
         */
        protected function getCachedDocument($keyword)
        {
            $result = $this->cache->get($keyword);
            if ($result) {
                return unserialize($result);
            }
            return null;
        }

        /**
         * @param string $keyword
         * @param Document $document
         */
        protected function cacheDocument($keyword, Document $document)
        {
            $this->cache->set($keyword, serialize($document), self::CACHE_LIFETIME);
        }

        /**
         * @param bool|false $htmlEscaped
         * @return bool|string
         */
        public static function getSearchString($htmlEscaped = false)
        {
            if (array_key_exists('search', $_POST)) {
                if ($htmlEscaped) {
                    return htmlentities($_POST['search'], ENT_QUOTES, 'utf-8');
                } else {
                    return $_POST['search'];
                }
            }
            return false;
        }
    }
}