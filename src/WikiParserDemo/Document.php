<?php
namespace WikiParserDemo {

    class Document
    {
        protected $dirty = false;
        protected $title;
        protected $notFoundWhen;
        protected $createdWhen;
        protected $lastRevId;
        protected $wordCount;
        protected $content;

        /**
         * @return array
         */
        public function __sleep()
        {
            return array('title', 'notFoundWhen', 'createdWhen', 'lastRevId', 'wordCount', 'content');
        }

        /**
         * @return boolean
         */
        public function isDirty()
        {
            return $this->dirty;
        }

        /**
         *
         */
        public function setDirty()
        {
            $this->dirty = true;
        }

        /**
         * @return mixed
         */
        public function getTitle()
        {
            return $this->title;
        }

        /**
         * @param mixed $title
         */
        public function setTitle($title)
        {
            $this->title = $title;
        }

        /**
         * @return mixed
         */
        public function getNotFoundWhen()
        {
            return $this->notFoundWhen;
        }

        /**
         * @param mixed $notFoundWhen
         */
        public function setNotFoundWhen($notFoundWhen)
        {
            $this->notFoundWhen = $notFoundWhen;
        }

        /**
         *
         */
        public function setNotFoundNow()
        {
            $this->notFoundWhen = time();
        }

        /**
         * @return mixed
         */
        public function getCreatedWhen()
        {
            return $this->createdWhen;
        }

        /**
         * @param mixed $createdWhen
         */
        public function setCreatedWhen($createdWhen)
        {
            $this->createdWhen = $createdWhen;
        }

        /**
         * @return mixed
         */
        public function getLastRevId()
        {
            return $this->lastRevId;
        }

        /**
         * @param mixed $lastRevId
         */
        public function setLastRevId($lastRevId)
        {
            $this->lastRevId = $lastRevId;
        }

        /**
         * @return mixed
         */
        public function getWordCount()
        {
            return $this->wordCount;
        }

        /**
         * @param mixed $wordCount
         */
        public function setWordCount($wordCount)
        {
            $this->wordCount = $wordCount;
        }

        /**
         * @return mixed
         */
        public function getContent()
        {
            return $this->content;
        }

        /**
         * @param mixed $content
         */
        public function setContent($content)
        {
            $this->content = $content;
        }

        /**
         * @param string $title
         * @param string $content
         */
        public function update($title, $content)
        {
            $this->setNotFoundWhen(false);
            $this->setTitle($title);
            $content = TextTools::getInstance()->trashWikiMarkup($content);
            $this->setContent($content);
            $this->setWordCount(
                TextTools::getInstance()->countWords($content)
            );
            $this->setCreatedWhen(time());
        }
    }
}