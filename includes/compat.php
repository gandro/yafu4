<?php
// TODO: implement fnmatch (nonposix), str_getcsv (php<5.3)

if(!@ini_get('short_open_tag')) {
    class short_open_tags_filter extends php_user_filter {

        private $replacements = array(
            '<?'    => '<?php',
            '<?='   => '<?php echo ',
            '<?php' => '<?php' /* stupid, but we want long tags */
        );

        public function filter($in, $out, &$consumed, $closing) {
            $end = '  ';

            while ($bucket = stream_bucket_make_writeable($in)) {
                $end = substr($bucket->data, -2);

                $bucket->data = strtr($bucket->data, $this->replacements);
                $consumed += $bucket->datalen;
                stream_bucket_append($out, $bucket);
            }

            if(!$closing && ($end[1] == '<' || $end == '<?')) {
                return PSFS_FEED_ME;
            }
            return PSFS_PASS_ON;
        }
    }

    stream_filter_register('short_open_tags', 'short_open_tags_filter');
}

?>
