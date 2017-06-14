<?php

namespace bs\Flatpickr;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;
use bs\Flatpickr\assets\FlatpickrAsset;

class Widget extends InputWidget
{
    /**
     * Plugin settings
     * @link https://chmln.github.io/flatpickr/
     *
     * @var array
     */
    public $clientOptions = [];

    /**
     * @var string
     */
    public $locale;

    /**
     * @var string
     */
    public $plugin;

    /**
     * @var string
     */
    public $theme;
    
    /**
     * Disable input
     *
     * @var bool
     */
    public $disabled = false;

    /**
     * Show group buttons
     *
     * @var bool
     */
    public $groupBtnShow = false;

    /**
     * Buttons template
     *
     * @var string
     */
    public $groupBtnTemplate = '{toggle} {clear}';

    /**
     * Buttons
     *
     * @var array
     */
    public $groupBtn = [
        'toggle' => [
            'btnClass' => 'btn btn-default',
            'iconClass' => 'glyphicon glyphicon-calendar',
        ],
        'clear' => [
            'btnClass' => 'btn btn-default',
            'iconClass' => 'glyphicon glyphicon-remove',
        ],
    ];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->clientOptions['locale'] = $this->locale;

        if (!empty($this->plugin)) {
            switch ($this->plugin) {
                case 'confirmDate':
                    $plugin = "[new confirmDatePlugin({})]";
                    break;
                case 'weekSelect':
                    $plugin = "[new weekSelectPlugin({})]";
                    break;
            }
            $this->clientOptions['plugins'] = new JsExpression($plugin);
        }

        if (!empty($this->groupBtnShow)) {
            $this->clientOptions['wrap'] = true;
        } else {
            $this->clientOptions['wrap'] = false;
        }

        parent::init();
    }

    /**
     * @return string
     */
    public function run()
    {
        $this->registerClientScript();
        $content = '';
        $options['data-input'] = '';
        if ($this->disabled) {
            $options['disabled'] = 'disabled';
        }

        if ($this->groupBtnShow) {
            $content .= '<div class="flatpickr-' . $this->options['id'] . ' input-group">';

            if ($this->hasModel()) {
                $content .= Html::activeTextInput($this->model, $this->attribute, ArrayHelper::merge($this->options, $options));
            } else {
                $content .= Html::textInput($this->name, $this->value, ArrayHelper::merge($this->options, $options));
            }

            $content .= '<div class="input-group-btn">';
            if (preg_match_all('/{(toggle|clear)}/i', $this->groupBtnTemplate, $matches)) {
                foreach ($matches[1] as $btnName) {
                    $content .= $this->renderGroupBtn($btnName);
                }
            }
            $content .= '</div>';
            $content .= '</div>';
        } else {
            if ($this->hasModel()) {
                $content = Html::activeTextInput($this->model, $this->attribute, ArrayHelper::merge($this->options, $options));
            } else {
                $content = Html::textInput($this->name, $this->value, ArrayHelper::merge($this->options, $options));
            }
        }

        return $content;
    }

    /**
     * Register widget client scripts.
     */
    protected function registerClientScript()
    {
        $view = $this->getView();
        $asset = FlatpickrAsset::register($view);

        $asset->locale = $this->locale;
        $asset->plugin = $this->plugin;
        $asset->theme = $this->theme;

        if ($this->groupBtnShow) {
            $selector = Json::encode('.flatpickr-' . $this->options['id']);
        } else {
            $selector = Json::encode('#' . $this->options['id']);
        }

        $options = !empty($this->clientOptions) ? Json::encode($this->clientOptions) : '';

        $view->registerJs("flatpickr($selector, {$options});");
    }

    /**
     * @param string $btnName
     * @return string
     */
    private function renderGroupBtn($btnName)
    {
        $content = '';
        if (isset($this->groupBtn[$btnName])) {
            if (isset($this->groupBtn[$btnName]['btnClass'])) {
                $btnClass = $this->groupBtn[$btnName]['btnClass'];
            } else {
                $btnClass = 'btn btn-default';
            }

            if (isset($this->groupBtn[$btnName]['iconClass'])) {
                $iconClass = $this->groupBtn[$btnName]['iconClass'];
            } else {
                $iconClass = '';
            }

            $disabled = '';
            if ($this->disabled) {
                $disabled = 'disabled="disabled"';
            }

            $content = <<<HTML
                <button class="$btnClass" type="button" $disabled data-$btnName>
                    <span class="$iconClass"></span>
                </button>
HTML;
        }

        return $content;
    }
}
