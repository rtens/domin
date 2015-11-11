<?php
namespace rtens\domin\delivery\web\renderers\charting;

abstract class Chart {

    private $options = [];

    /** @var ColorProvider */
    protected $colors;

    public function __construct() {
        $this->colors = new ColorProvider();
    }

    /**
     * @return array
     */
    protected function getDefaultOptions() {
        return [
            'animation' => false,
            'responsive' => true,
            'multiTooltipTemplate' => '<%=datasetLabel%>: <%=value%>',
        ];
    }

    /**
     * @return array
     */
    public function getOptions() {
        return array_merge($this->getDefaultOptions(), $this->options);
    }

    /**
     * @param array $options Merges current options with given options
     * @return $this
     */
    public function setOptions(array $options) {
        $this->options = $options;
        return $this;
    }

    abstract public function chartType();

    abstract public function makePalette(array $rgb);

    abstract public function data();

}