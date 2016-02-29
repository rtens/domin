<?php
namespace rtens\domin\delivery\web\home;

use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\WebRenderer;

class ActionListRenderer implements WebRenderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof ActionList;
    }

    /**
     * @param ActionList $value
     * @return mixed
     */
    public function render($value) {
        $elements = [];

        if ($value->hasGroups()) {
            foreach ($value->getGroups() as $group) {
                $elements[] = $this->renderGroup($group, $value->getActionsOf($group));
            }
            $elements[] = $this->renderGroup('All', $value->getAllActions());
            $elements[] = $this->collapseScript();
        } else {
            $elements[] = $this->renderList($value->getAllActions());
        }

        return new Element('div', [], $elements);
    }

    /**
     * @param ActionList $value
     * @return array|Element[]
     */
    public function headElements($value) {
        return [
            HeadElements::jquery(),
            HeadElements::bootstrap(),
            HeadElements::bootstrapJs(),
        ];
    }

    private function renderGroup($group, $actions) {
        return new Element('div', ['class' => 'action-group'], [
            new Element('h2', ['class' => 'group-name'], [
                new Element('small', [], [
                    new Element('span', ['class' => 'toggle-group glyphicon glyphicon-chevron-right']),
                    new Element('span', ['class' => 'toggle-group glyphicon glyphicon-chevron-down', 'style' => 'display: none;'])
                ]),
                $group
            ]),
            $this->renderList($actions)
        ]);
    }

    /**
     * @param ActionListItem[] $actions
     * @return Element
     */
    private function renderList($actions) {
        $items = [];
        foreach ($actions as $action) {
            $items[] = new Element('a', [
                'href' => $action->getId(),
                'class' => 'list-group-item'
            ], [
                $action->getCaption()
            ]);
        }
        return new Element('div', ['class' => 'list-group'], $items);
    }

    private function collapseScript() {
        return "
            <script>
                $(function () {
                    $('.list-group').hide();

                    var groupName = $('.group-name');
                    groupName.css('cursor', 'pointer');
                    groupName.click(function () {
                        $(this).closest('.action-group').find('.list-group').toggle();
                        $(this).find('.toggle-group').toggle();
                    });
                });
            </script>";
    }
}