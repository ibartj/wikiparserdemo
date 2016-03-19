<?php
namespace WikiParserDemo {
    class TextTools
    {
        /**
         * @var array
         */
        private $patterns = array(
            "/\r\n/",

            // Headings
            "/^==== (.+?) ====$/m",                        // Subsubheading
            "/^=== (.+?) ===$/m",                        // Subheading
            "/^== (.+?) ==$/m",                        // Heading

            // Formatting
            "/\'\'\'\'\'(.+?)\'\'\'\'\'/s",                    // Bold-italic
            "/\'\'\'(.+?)\'\'\'/s",                        // Bold
            "/\'\'(.+?)\'\'/s",                        // Italic

            // Special
            "/^----+(\s*)$/m",                        // Horizontal line
            "/\[\[(file|img):((ht|f)tp(s?):\/\/(.+?))( (.+))*\]\]/i",    // (File|img):(http|https|ftp) aka image
            "/\[((news|(ht|f)tp(s?)|irc):\/\/(.+?))( (.+))\]/i",        // Other urls with text
            "/\[((news|(ht|f)tp(s?)|irc):\/\/(.+?))\]/i",            // Other urls without text

            // Indentations
            "/[\n\r]: *.+([\n\r]:+.+)*/",                    // Indentation first pass
            "/^:(?!:) *(.+)$/m",                        // Indentation second pass
            "/([\n\r]:: *.+)+/",                        // Subindentation first pass
            "/^:: *(.+)$/m",                        // Subindentation second pass

            // Ordered list
            "/[\n\r]?#.+([\n|\r]#.+)+/",                    // First pass, finding all blocks
            "/[\n\r]#(?!#) *(.+)(([\n\r]#{2,}.+)+)/",            // List item with sub items of 2 or more
            "/[\n\r]#{2}(?!#) *(.+)(([\n\r]#{3,}.+)+)/",            // List item with sub items of 3 or more
            "/[\n\r]#{3}(?!#) *(.+)(([\n\r]#{4,}.+)+)/",            // List item with sub items of 4 or more

            // Unordered list
            "/[\n\r]?\*.+([\n|\r]\*.+)+/",                    // First pass, finding all blocks
            "/[\n\r]\*(?!\*) *(.+)(([\n\r]\*{2,}.+)+)/",            // List item with sub items of 2 or more
            "/[\n\r]\*{2}(?!\*) *(.+)(([\n\r]\*{3,}.+)+)/",            // List item with sub items of 3 or more
            "/[\n\r]\*{3}(?!\*) *(.+)(([\n\r]\*{4,}.+)+)/",            // List item with sub items of 4 or more

            // List items
            "/^[#\*]+ *(.+)$/m",                        // Wraps all list items to <li/>
            // TOC
            '/__TOC__/'
        );

        /**
         * @var TextTools
         */
        private static $instance;

        /**
         *
         */
        private function __construct()
        {
        }

        /**
         * @return TextTools
         */
        public static function getInstance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        /**
         * @param string $text
         * @return string
         */
        public function trashWikiMarkup($text)
        {
            if (preg_match('~^[#]redirect \[\[(.+)\]\]$~i', $text, $capture)) {
                return $text;
            }
            // panels
            while (strpos($text, '{{') !== false && strpos($text, '}}') !== false) {
                $text = preg_replace('~\{\{[^{]+?\}\}~s', '', $text);
            }
            // patterns
            $text = preg_replace($this->patterns, '$1', $text);
            // links
            $text = preg_replace('~\[\[([^\|\]\[\:]+[\:])?([^\|\]\[\:]+[\|\:])?([^\|\]\[]+)\]\]~', '$3', $text);
            // images
            $text = preg_replace('~\[\[[^\]|]+\|[^\]|]+\|([^\]|]+\|)?([^\]]+)\]\]~s', '$2', $text);
            // tags
            $text = strip_tags($text);
            // non-breaking spaces
            $text = str_replace('&nbsp;', ' ', $text);
            // lines
            $text = preg_replace('~\n{2,}~', "\n\n", $text);
            // links
            $text = preg_replace('~http\://\S+~', '', $text);
            return $text;
        }

        /**
         * @param string $text
         * @return int
         */
        public function countWords($text)
        {
            $text = $this->normalizeText($text);
            $words = preg_split('~\s+~', $text);
            return count(array_flip($words));
        }

        /**
         * @param string $text
         * @return string
         */
        protected function normalizeText($text)
        {
            $text = preg_replace('~[\s\P{L}]+~u', ' ', $text);
            $text = mb_strtolower($text, 'utf-8');
            return $text;
        }
    }
}