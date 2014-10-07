<?php
namespace Ilmatar;

class TagManager
{
    private $strategies;
    private $options;
    
    /**
     * Construct
     * 
     * @param Array $strategies
     */
    public function __construct(array $strategies, array $options)
    {
        $this->options    = $options;
        $this->strategies = [];
        foreach ($strategies["app.tags.strategy"] as $strategyParams) {
            $this->strategies[$strategyParams['key']] = $strategyParams['value'];
        }
    }

    /**
     * Replaces all tags defined in strategies into a string
     * 
     * @param string $content
     * @return string
     */
    public function replaceTags($content)
    {
        foreach ($this->strategies as $tag => $strategy) {
            if (false !== stripos($content, $tag)) {
                $strategy = new $strategy($this->options);
                $content = str_ireplace($tag, $strategy->format($tag), $content);
            }
        }
        return $content;
    }
}
