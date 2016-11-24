<?php

namespace App\Http\Controllers;

use JsonSerializable;

class Attachments implements JsonSerializable
{
    /** @var array */
    protected $url;

    /** @var string */
    protected $text;

    /**
     * @param string $text
     *
     * @return static
     */
    public static function create($text)
    {
        return new static($text);
    }

    /**
     * @param string $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * @param array $buttons
     * @return $this
     */
    public function addUrl($image)
    {
        $this->url = $image;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'text' => $this->text,
            'image_url' => $this->url, 
        ];
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}