<?php
namespace uuf6429\Prune\Util;

class DocBlockParser
{
    protected $raw;
    protected $tags;
    protected $comment;

    /**
     * @param string $docBlock
     */
    public function __construct($docBlock)
    {
        $this->parse($docBlock);
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @param bool $asArray
     *
     * @return mixed
     */
    public function getTag($name, $default = null, $asArray = false)
    {
        if (!isset($this->tags[$name])) {
            return $default;
        }

        return ($asArray && !is_array($this->tags[$name]))
            ? [$this->tags[$name]] : $this->tags[$name];
    }

    /**
     * @param string
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getTag($name);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function tagExists($name)
    {
        return isset($this->tags[$name]);
    }

    /**
     * @param string $raw
     */
    protected function parse($raw)
    {
        $this->raw = $raw;
        $raw = str_replace("\r\n", "\n", $raw);
        $lines = explode("\n", $raw);

        if (count($lines) < 3) {
            return;
        }

        $start = array_shift($lines);
        $end = array_pop($lines);
        $in_comment = true;

        foreach ($lines as $line) {
            $line = preg_replace('#^[ \t\*]*#', '', $line);

            if (strlen($line) < 2) {
                continue;
            }

            if (preg_match('#@([^ ]+)(.*)#', $line, $matches)) {
                $in_comment = false;
                $tag_name = $matches[1];
                $tag_value = trim($matches[2]);

                // If this tag was already parsed, make its value an array
                if (isset($this->tags[$tag_name])) {
                    if (!is_array($this->tags[$tag_name])) {
                        $this->tags[$tag_name] = [$this->tags[$tag_name]];
                    }

                    $this->tags[$tag_name][] = $tag_value;
                } else {
                    $this->tags[$tag_name] = $tag_value;
                }
                continue;
            }

            $this->comment .= "$line\n";
        }

        $this->comment = trim($this->comment);
    }
}
