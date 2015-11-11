<?php
namespace rtens\domin\delivery\web\renderers\charting;

use rtens\domin\delivery\web\renderers\charting\coloring\ColorProvider;
use rtens\domin\delivery\web\renderers\charting\coloring\MixedColorProvider;
use rtens\domin\parameters\Color;

abstract class Chart {

    private $options = [];

    /** @var ColorProvider */
    private $colors;

    public function __construct() {
        $this->colors = new MixedColorProvider();
    }

    /**
     * @param ColorProvider $colors
     * @return $this
     */
    public function injectColorProvider(ColorProvider $colors) {
        $this->colors = $colors;
        return $this;
    }

    protected function provideColor() {
        return $this->colors->next();
    }

    /**
     * @return array
     */
    protected function defaultOptions() {
        return [
            'animation' => false,
            'responsive' => true,
            'multiTooltipTemplate' => '<%=datasetLabel%>: <%=value%>',
        ];
    }

    /**
     * @return array
     */
    public function options() {
        return array_merge($this->defaultOptions(), $this->options);
    }

    /**
     * @param array $options Merges current options with given options
     * @return $this
     */
    public function changeOptions(array $options) {
        $this->options = $options;
        return $this;
    }

    abstract public function chartType();

    abstract public function makePalette(Color $color);

    abstract public function data();

}