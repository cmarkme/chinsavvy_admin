<?php
/**
 * Class for building and displaying flot graphs
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
class Flotgraph {
    /**
     * @var string $id A unique DOM id
     */
    public $id;
    public $data = array();
    public $options;
    public $toggleseries=false;
    public $choicecontainer;
    public $showtooltips=false;

    public function initialise($id, $data, $options, $choicecontainer=null, $toggleseries=false, $showtooltips=true) {

        if (!empty($toggleseries)) {
            $this->toggleseries = $toggleseries;
        }
        if (!empty($showtooltips)) {
            $this->showtooltips = $showtooltips;
        }
        if (!empty($choicecontainer)) {
            $this->choicecontainer = $choicecontainer;
        }

        $this->options = new graph_options($options);
        $this->data = $data;
        $this->id = $id;
    }

    public function get_JS() {
        $html = '<script type="text/javascript">
		 /* <![CDATA[ */
		'.$this->get_JS_withoutTags().'
		/* ]]>*/
		</script>';
		return $html;
    }

	public function get_JS_withoutTags() {
		$extra_functions = $this->get_JS_functions();

        $html = '
            var data' . $this->id . ' = ' . json_encode($this->data) . ';

            var options' . $this->id . ' = ' . $this->get_json_options() . ';';


        if ($this->toggleseries) {
            $html .= '
            // Hard-code colour indices to prevent them from shifting as series are turned on/off
            var i = 0;
            $.each(data'.$this->id.', function(key, val) {
                val.color = i;
                ++i;
            });

            var choiceContainer = $("#'.$this->choicecontainer.'");
            choiceContainer.find("input").click(plotAccordingToChoices);

            function plotAccordingToChoices() {
                var data = [];

                choiceContainer.find("input:checked").each(function() {
                    var key = $(this).attr("name");
                    if (key && data'.$this->id.'[key]) {
                        data.push(data'.$this->id.'[key]);
                    }
                });

                if (data.length > 0) {
                    $.plot($("#' . $this->id . '"), data, options' . $this->id . ');
                }
            }

            plotAccordingToChoices();
            ';
        } else {
            $html .= '

            $(document).ready(function() {
                $.plot($("#' . $this->id . '"), data' . $this->id . ', options' . $this->id . ');
            });
            ';
        }

        $html .= $extra_functions . '
			';
        return $html;
	}

    /**
     * Returns formatted JSON options (mainly stripping double quotes)
     */
    public function get_json_options() {
        $json_options = json_encode($this->options);
        $json_options = preg_replace('/("tickFormatter"\:)"([a-z_-]*)"/', '$1 $2', $json_options);
        $json_options = preg_replace('/("labelFormatter"\:)"([a-z_-]*)"/', '$1 $2', $json_options);
        $json_options = preg_replace('/("coloredAreas"\:)"([a-z_-]*)"/', '$1 $2', $json_options);
        $json_options = preg_replace('/("ticks"\:)"([a-z_-]*)"/', '$1 $2', $json_options);
        $json_options = preg_replace('/"([a-zA-Z]*)"\:/', '$1:', $json_options);
        return $json_options;
    }

    public function get_JS_functions() {
        $js = '
            function currency_formatter(val, axis) {
                var num = new NumberFormat();
                num.setNumber(val);
                num.setCurrencyValue("$");
                num.setCurrency(true);
                num.setSeparators(true, ",", ",");
                num.setPlaces(axis.tickDecimals);
                return num.toFormatted();
            }';

        if ($this->showtooltips) {
            $js .= "
                function showToolTip(x, y, contents) {
                    var tooltip = document.createElement('div');
                    $(tooltip).attr('id', 'tooltip');
                    $(tooltip).html(contents);
                    $(tooltip).css( {
                        position: 'absolute',
                        top: y + 5,
                        left: x + 5,
                        border: '1px solid #fdd',
                        padding: '2px',
                        'background-color': '#fee',
                        opacity: 0.80
                    });
                    $(tooltip).appendTo('body').fadeIn(200);
                }

                var previousPoint = null;

                $('#$this->id').bind('plothover', function (event, pos, item) {
                    $('#x').text(pos.x.toFixed(2));
                    $('#y').text(pos.y.toFixed(2));

                    if (item) {
                        if (previousPoint != item.datapoint) {
                            previousPoint = item.datapoint;

                            $('#tooltip').remove();

                            var x = item.datapoint[0];
                            var y = item.datapoint[1].toFixed(2);
                            var d = new Date(x);

                            showToolTip(item.pageX, item.pageY, item.series.label + ' value at ' + d.toDateString() + ' = ' + y);
                        }
                    } else {
                        $('#tooltip').remove();
                        previousPoint = null;
                    }
                });
            ";
        }

        return $js;
    }
}

class graph_options {
    public $lines;
    public $points;
    public $bars;
    public $xaxis;
    public $yaxis;
    public $legend;
    public $grid;
    public $selection;
    public $colors = array("#edc240", "#afd8f8", "#cb4b4b", "#4da74d", "#9440ed");

    public function __construct($options = null) {
        $this->lines = new graph_option_lines($options);
        $this->points = new graph_option_points($options);
        $this->xaxis = new graph_option_xaxis($options);
        $this->yaxis = new graph_option_yaxis($options);
        $this->legend = new graph_option_legend($options);
        $this->bars = new graph_option_bars($options);
        $this->grid = new graph_option_grid($options);
        $this->selection = new graph_option_selection($options);
    }
}

class graph_option {
    public function __construct($options = null) {
        preg_match('/graph_option_([a-z]*)/', get_class($this), $matches);

        if (!empty($options[$matches[1]]) && is_array($options[$matches[1]])) {
            foreach ($options[$matches[1]] as $option => $value) {
                if (array_key_exists($option, get_object_vars($this))) {
                    $this->$option = $value;
                }
            }
        }
    }
}

class graph_option_lines extends graph_option {
    public $show = false;
    public $lineWidth = 2;
    public $fill = false;
    public $fillColor = null;
}

class graph_option_points extends graph_option {
    public $show = false;
    public $radius = 3;
    public $lineWidth = 2; // in pixels
    public $fill = true;
    public $fillColor = "#ffffff";
}

class graph_option_bars extends graph_option {
    public $show = false;
    public $lineWidth = 2; // in pixels
    public $barWidth = 1; // In xaxis units, not pixels!
    public $fill = true;
    public $fillColor = null;
}

class graph_option_xaxis extends graph_option {
    public $mode = null; // null or "time"
    public $min = null; // min. value to show, null means set automatically
    public $max = null; // max. value to show, null means set automatically
    public $autoscaleMargin = null; // margin in % to add if auto-setting min/max
    public $ticks = 12; // either [1, 3] or [[1, "a"], 3] or (fn: axis info -> ticks) or app. number of ticks for auto-ticks
    public $tickFormatter = null; // fn: number -> string

    // mode specific options
    public $tickDecimals = null; // no. of decimals, null means auto
    public $tickSize = null; // number or [number, "unit"]
    public $minTickSize = null; // number or [number, "unit"]
    public $monthNames = null; // list of names of months
    public $timeformat = null; // format string to use
}

class graph_option_yaxis extends graph_option_xaxis{
    public $autoscaleMargin = 0.02;
}

class graph_option_legend extends graph_option {
    public $show = true;
    public $noColumns = 1; // number of colums in legend table
    public $labelFormatter = null; // fn: string -> string
    public $labelBoxBorderColor = "#ccc"; // border color for the little label boxes
    public $container = null; // container (as jQuery object) to put legend in, null means default on top of graph
    public $position = "ne"; // position of default legend container within plot
    public $margin = 5; // distance from grid edge to default legend container within plot
    public $backgroundColor = null; // null means auto-detect
    public $backgroundOpacity = 0.85; // set to 0 to avoid background
}

class graph_option_grid extends graph_option {
    public $color = "#545454"; // primary color used for outline and labels
    public $backgroundColor = null; // null for transparent, else color
    public $tickColor = "#dddddd"; // color used for the ticks
    public $labelMargin = 3; // in pixels
    public $borderWidth = 2;
    public $clickable = null;
    public $hoverable = null;
    public $coloredAreas = null; // array of { x1, y1, x2, y2 } or fn: plot area -> areas
    public $coloredAreasColor = "#f4f4f4";
}

class graph_option_selection extends graph_option {
    public $mode = null; // one of null, "x", "y" or "xy"
    public $color = "#e8cfac";
}
?>
