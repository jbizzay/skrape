<?php
namespace Skrape\Meta;

class Meta
{
    /**
     * Data set on this meta
     * @var array
     */
    protected $data = [];

    /**
     * @param string|array $keyOrArray
     * @param any $value
     */
    public function __construct($keyOrArray = [], $value = null)
    {
        $this->data = self::setData($this->data, $keyOrArray, $value);
    }

    /**
     * Get meta data
     * @param string $key
     * @return any
     */
    public function get($key = null)
    {
        return self::lookup($this->data, $key);
    }

    /**
     * Set data on this meta, e.g:
     *
     * $meta->set('namespace', ['key' => 'value']);
     * $meta->set('namespace.key', 'value');
     * $meta->set(['namespace' => ['key' => 'value']]);
     *
     * @param string|array $keyOrArray
     * @param mixed $data
     * @return Object $this
     */
    public function set($keyOrArray, $data = null)
    {
        $this->data = self::setData($this->data, $keyOrArray, $data);
        return $this;
    }

    /**
     * Get data out of an array by key or dot notation
     * @param array $options
     * @param string $key
     * @return any config value
     */
    protected static function lookup(array $options, $key = null)
    {
        if ($key) {
            $parts = explode('.', $key);
            $count = count($parts);
            $current = $options;
            foreach ($parts as $i => $part) {
                if ( ! isset($current[$part])) {
                    return null;
                }
                if ($i + 1 == $count) {
                    return $current[$part];
                }
                $current = $current[$part];
            }
        }
        return $options;
    }

    /**
     * Set data on an array
     * @param array $source
     * @param string|array $keyOrArray
     * @param any $data
     * @return array $source
     */
    protected static function setData(array $source, $keyOrArray, $data)
    {
        if (is_array($keyOrArray)) {
            $source = array_replace_recursive($source, $keyOrArray);
        } elseif (is_string($keyOrArray)) {
            $refSource = &$source;
            $parts = explode('.', $keyOrArray);
            $count = count($parts);
            foreach ($parts as $i => $part) {
                if ( ! isset($refSource[$part])) {
                    $refSource[$part] = [];
                }
                if ($i + 1 == $count) {
                    if (is_array($data)) {
                        $refSource[$part] = array_replace_recursive($refSource[$part], $data);
                    } else {
                        $refSource[$part] = $data;
                    }
                    continue;
                }
                $refSource = &$refSource[$part];
            }
        }
        return $source;
    }
}
