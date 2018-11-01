<?PHP
/**
* @package pdf_gantt
* @name pdf_gantt.php, contains class PdfGantt,
* for generating PDF documents with Gantt charts.
* @uses TCPDF (printing to PDF document)
* @Author Alexander Selifonov, <alex [at] selifan {dot} ru>
* @version 0.90.0018 2013-04-26
* @Link: http://www.selifan.ru
* @Link: http://www.phpclasses.org/browse/author/267915.html
* @license http://www.opensource.org/licenses/bsd-license.php    BSD
*
**/

namespace GO\Base\Util;


class PdfGantt {
    const DEFAULT_MARGIN = 5;
    const DEFAULT_DATEFORMAT = 'd.m.Y';
    static $_show_taskid = FALSE;

    private $_locStrings = array(
         'days'      => '%s day(s)'
        ,'task'      => 'task'
        ,'subtask'   => 'subtask'
        ,'subtasks'  => 'subtasks'
        ,'milestone' => 'milestone'
        ,'milestones'=> 'Milestones'
    );
    private $_data = array(); # _data['title'] - general title,  _data['tasks'] - array of tasks to be rendered
    private $_tcpdf = null;
    private $_curstate = array();
    private $_milestones = array();
    private $_config = array(
        'stringcharset'=>'UTF-8'
       ,'dateformat'  => 'M Y'  # date format for timeline titles : default creates titles like "Jan 2013"
       ,'dateformat2' => 'm/j'  # date format for task start dates
       ,'title_fontsize' => 8   # main title font size
       ,'show_taskdays' => 1
       ,'descr_width' => 0.20   # "description" column width (relational or abs.)
       ,'bgcolor'     => ''     # background color for the whole chart area
       ,'grid_color'  => '#888' # chart grid color
       ,'box_bgcolor' => '#009cc5' # background for "task" bars
       ,'box_bgcolor2'=> '#ccf' # background for "undone" part if 'progress' less than 1
       ,'box_bgcolor3'=> ''     # background for overdue tasks, by default don't show overdue (NOT IMPLEMENTED!)
       ,'ms_color'    => '#4ff' # milestone "gem" fill color
       ,'box_border'  => '#111' # border color for "task" bars and milestone gems
       ,'text_color'  => '#000' # text main color
       ,'arrow_color' => '#777' # arrows showing dependencies
       ,'dates_fontsize' =>  6  # dates font size
       ,'taskdescr_fontsize'=>7 # people in task font size
       ,'members_fontsize' => 5.5 # people in task font size
       ,'shade_color' => '' # shadow background color (no shade by default)
       ,'shade_offsetx' => 1.0 # shadow offsets
       ,'shade_offsety' => 0.7
       ,'ms_height'     => 0.08 # part of chart height to print milestones
       ,'ms_fontsize'   => 6.0  # font size for milestone titles
    );
    var $_pos = array(0,0); # left, top positiion of render area
    var $_dim = array(0,0); # width, height of render area

    var $_error_message = 'unknown error';
    public function __construct($tcpdfobj, $cfg = null, $x=0,$y=0,$w=0,$h=0) {
        $this->_tcpdf = $tcpdfobj;
        $this->_error_message = '';
        if(is_array($cfg)) $this->setConfig($cfg);
        $this->_pos = array($x,$y);
        $this->_dim = array($w,$h);
    }

    /**
    * Localizing output strings like task, days, etc.
    *
    * @param mixed $strarr assoc.array with new string values
    */
    public function localize($strarr) {
        if(is_array($strarr)) foreach(array_keys($this->_locStrings) as $key) {
            if(!empty($strarr[$key])) $this->_locStrings[$key] = $strarr[$key];
        }
        return $this;
    }
    /**
    * Sets area for gannt bar
    *
    * @param mixed $x start x pos
    * @param mixed $y start y pos
    * @param mixed $w width
    * @param mixed $h geight
    * @return PdfGantt
    */
    public function setAreaPosition($x, $y, $w=0, $h=0) {
        $this->_pos = array(floatval($x),floatval($y));
        $this->_dim = array(floatval($w),floatval($h));
        return $this;
    }
    public function setData($data) {
        $this->_data = $data;
        return $this;
    }
    public function setConfig($cfg) {
        if(is_array($cfg)) $this->_config = array_merge($this->_config, $cfg);
        return $this;
    }
    public function getErrorMessage() { return $this->_error_message; }
    /**
    * convert string date 'yyyy-mm-dd' to dateTime var
    *
    * @param mixed $chdate
    */
    public static function char2date($chdate) {
        $darr = preg_split("/[\s,-\/\.\:]+/",$chdate);
        $ret = 0;
        if(count($darr)>=3) {
           $year = ($darr[0]<=31) ? $darr[2] : $darr[0];
           $mon = $darr[1];
           $day = ($darr[0]<=31) ? $darr[0] : $darr[2];
           if($year<15) $year+=2000; # correct "2-digit" year
           $ret = @mktime(1,0,0,$mon,$day,$year);
        }
        return $ret;
    }
    /**
    * calculates amount of days between two dates (in 'YYYY-MM-DD' string values or times)
    *
    * @param mixed $date1 date 1
    * @param mixed $date2 date 2
    */
    public static function daysBetween($date1, $date2) {
        $dt1 = is_string($date1) ? self::char2date($date1) : $date1;
        $dt2 = is_string($date2) ? self::char2date($date2) : $date2;
        return floor(($dt2 - $dt1)/86400);
    }
    public static function addDays($date1, $days) {
        $dt1 = is_string($date1) ? self::char2date($date1) : $date1;
        $ret = $dt1 + ($days*86400);
        return (is_string($date1) ? date('Y-m-d',$ret) : $ret);
    }
    private function _convertCset($strval) {
        $ret = ($this->_config['stringcharset']!='' && $this->_config['stringcharset']!='UTF-8') ?
          @iconv($this->_config['stringcharset'],'UTF-8',$strval) : $strval;
        return $ret;
    }
    /**
    * Prints Gannt chart for passed task data
    *
    * @param mixed $tcpdf_obj TCPDF instance
    */
    public function Render($data=null) {
        if($data && is_array($data)) $this->setData($data);
        if(!($this->_tcpdf instanceof TCPDF)) {
            $this->_error_message = 'Passed parameter is not TCPDF instance, rendering impossible!';
            return false;
        }
        $this->_saveCurrentPdfState(); #  $curfontSize = $this->_tcpdf->getFontSize();
        $this->_tcpdf->setPageUnit('mm'); # we work in millimeters!

        $width = (float)$this->_dim[0];
        $startx = floatval($this->_pos[0]);
        $starty = floatval($this->_pos[1]);
        $height = (float)$this->_dim[1];
        $endy = min($this->_tcpdf->getPageHeight(), $starty+$height);
        $height = $endy - $starty;
        $drange = array(0,0);
        if(isset($this->_data['daterange'])) {
            $drarr = is_string($this->_data['daterange']) ? explode(',',$this->_data['daterange']) : $this->_data['daterange'];
            $drange[0] = $drarr[0];
            if(isset($drarr[1])) $drange[1] = $drarr[1];
        }
        $datestart = $drange[0] ? self::char2date($drange[0]) : 0;
        $dateend   = $drange[1] ? self::char2date($drange[1]) : 0;
        $auto_start = ($datestart==0);
        $auto_end  = ($dateend==0);

        $rawdata = array();
        # Create "raw data" for rendering
        if(isset($this->_data['items']) && is_array($this->_data['items'])) {
            foreach($this->_data['items'] as $rawno => $item) {
                $item_id = isset($item['id']) ? $item['id'] : 'item_'.$rawno;
                $descr   = isset($item['description']) ? $item['description'] : $item_id;
                $dt1  = isset($item['datestart']) ? self::char2date($item['datestart']) : 0;
                $depend = isset($item['dependencies']) ? $item['dependencies'] : '';
                $dt2 = isset($item['dateend']) ? self::char2date($item['datestart']) : 0;
                $days = (isset($item['workdays'])) ? intval($item['workdays']) : false;

                $progress = isset($item['progress']) ? floatval($item['progress']) : 1;
                $members  = isset($item['members']) ? $item['members'] : '';
                $color  = isset($item['color']) ? $item['color'] : '';
                $mcolor  = isset($item['mcolor']) ? $item['mcolor'] : '';
                $mstone = isset($item['milestone']) ? $item['milestone'] : false;
                if(isset($item['dateend']) && !empty($dt1)) {
                    $dt2 = self::char2date($item['dateend']);
                    if($dt1>0) $days = self::daysBetween($dt1,$dt2)+1; # jan.01..jan.02 = 2 workdays: (1 day difference)
                }
                if($dt1>0 && $days>0 && empty($dt2)) {
                    $dt2 = self::addDays($dt1, $days-1);
                }
                if(!$item_id) continue; # empty ID not allowed
                if(($dt1>0 && $dt2>=$dt2) OR ($days>0 && !empty($depend))) {
                    $rawdata[$item_id] = array('id'=>$item_id, 'description'=>$descr,'datestart'=>$dt1
                        ,'dateend'=>$dt2, 'workdays'=>$days, 'dependencies'=>$depend,'progress'=>$progress
                        ,'members'=>$members,'color'=>$color, 'mcolor'=>$mcolor, 'milestone'=>$mstone
                    );
                }
            }
        }

        # adjust start dates of "dependent" tasks, by shifting them after "parent" tasks
        $b_dependencies = false;
        foreach($rawdata as $itemid => $rd) {
            $dt1 = $rd['datestart']; $dt2 = $rd['dateend'];
            if(!empty($rd['dependencies'])) {
                $darr = is_string($rd['dependencies']) ? explode(',',$rd['dependencies']) : $rd['dependencies'];
                if(!empty($darr[0])) foreach($darr as $taskid) {
                    $b_dependencies = true;
                    if($taskid != $itemid && isset($rawdata[$taskid]['dateend'])) {
                        $dt1 = $rawdata[$itemid]['datestart'] = max($rawdata[$itemid]['datestart'], $rawdata[$taskid]['dateend']+86400);
                        $dt2 = $rawdata[$itemid]['dateend'] = $rawdata[$itemid]['datestart'] +86400*($rawdata[$itemid]['workdays']-1);
                        # auto-detect start and end date of a chart
                    }
                }
            }
            if($auto_start) $datestart = ($datestart==0) ? $dt1 : min($datestart,$dt1);
            if($auto_end)   $dateend   = ($dateend==0) ? $dt2 : max($dateend,$dt2);
            if(!empty($rd['milestone'])) {
                $ds = $rawdata[$itemid]['datestart'];
                if(isset($this->_milestones[$ds])) $this->_milestones[$ds][] = $rd['milestone'];
                else $this->_milestones[$ds] = array($rd['milestone']);
                # if two or more milestones have the same start date, they will be joined into one, and it's title name will contain all titles
            }
        }

        if($auto_start) { # make shure area starts with 1 day of month
            $dom = date('d',$datestart);
            if($dom>1) $datestart -= 86400*($dom-1);
        }
        if($auto_end) { # make shure timeline finishes at 1st day of next month after the last task completion
            while(date('d',$dateend)!=1) { $dateend += 86400; }
        }

#        $this->_tcpdf->Line(($startx-2),$starty,$startx-2, $endy); # debug line

        # Now $rawdata ready to render, date range calculated ($datestart, $dateend)
        if($width<=0) { # if positive width not defined, stretch rendering to ALL available area.
            $width = $this->_tcpdf->getPageWidth() - $startx - self::DEFAULT_MARGIN;
        }
        if($height<=0) { # the same with height
            $height = $this->_tcpdf->getPageHeight() - $starty - self::DEFAULT_MARGIN;
        }
        # auto-convert title to UTF-8 if needed.
        $title = isset($this->_data['title']) ? $this->_convertCset(trim($this->_data['title'])) : '';

        $shift = $ms_height = 0;

        if($title) {
            $shift = 8;
            $this->_tcpdf->SetFont('', '', floatval($this->_config['title_fontsize']));
            $this->_tcpdf->MultiCell($width,$height, $title, 0, 'C', 0, 1, $startx, $starty );
        }
        if(count($this->_milestones)) {
            $ms_height = $this->_config['ms_height'];
            if($ms_height < 1) $ms_height = $height * $ms_height;
        }
        if($height-$shift-$ms_height < 16) {
            $this->_error_message = 'Too small height to render Gantt chart !';
            return false;
        }
        $descWidth = ($this->_config['descr_width'] < 1.0) ? round($width * $this->_config['descr_width'],2) : floatval($this->_config['descr_width']);
        $timeline_x = $startx + $descWidth+0.2;
        $timelineWidth = $width - $descWidth - 0.2;
        $ms_size = round($ms_height/4,2);

        $max_x = $startx + $width;

        $rgbText   = TCPDF_COLORS::convertHTMLColorToDec($this->_config['text_color'],$spotc);
        $rgbGrid   = TCPDF_COLORS::convertHTMLColorToDec($this->_config['grid_color'],$spotc);
        $rgbFill   = TCPDF_COLORS::convertHTMLColorToDec($this->_config['box_bgcolor'],$spotc);
        $rgbFill2  = TCPDF_COLORS::convertHTMLColorToDec($this->_config['box_bgcolor2'],$spotc);
        $rgbBorder = TCPDF_COLORS::convertHTMLColorToDec($this->_config['box_border'],$spotc);
        $msFill    = TCPDF_COLORS::convertHTMLColorToDec($this->_config['ms_color'],$spotc);
        $rgbShade  = $this->_config['shade_color'] ? TCPDF_COLORS::convertHTMLColorToDec($this->_config['shade_color'],$spotc) : FALSE;
        $lineStyle = array('width' => 0.1,  'dash' => 0, 'color' => $rgbGrid);

        $height -= $shift+$ms_height;
        if($ms_height>0) {
            $this->_tcpdf->SetFillColorArray($rgbFill);
            $this->_tcpdf->SetDrawColorArray($rgbGrid);
        }
        $grid_y = $starty + $shift;
        $milestone_y = $grid_y + $ms_height/2;
        $timeline_y = $grid_y + $ms_height; # gannt timeline top pos (first task row)

        $step_y = round($height / count($rawdata),2); # one row height in the grid

        $rgbBg = $this->_config['bgcolor'] ? TCPDF_COLORS::convertHTMLColorToDec($this->_config['bgcolor'],$spotc) : FALSE;
#        $boxBorderStyle = array('width' => 0.1,  'dash' => 0, 'color' => $rgbBorder);
        $this->_tcpdf->SetDrawColorArray($rgbGrid);
        if($rgbBg) {
            $this->_tcpdf->SetFillColorArray($rgbBg);
            $this->_tcpdf->Rect($startx,$grid_y, $width, $height+$ms_height, 'DF');
        }
        else $this->_tcpdf->Rect($startx,$grid_y, $width, $height+$ms_height, '', $lineStyle, array());

        if($descWidth>0) {
            $this->_tcpdf->Line(($startx+$descWidth),$grid_y,$timeline_x, ($grid_y+$height+$ms_height), $lineStyle);
        }

        $dayWidth = round(($max_x - $timeline_x) / ( $dateend - $datestart) * 86400, 3);

        # Draw all vertical bars for month beginnings, and their dates at the bottom:
        $dtstart = date($this->_config['dateformat'], $datestart);
        if(date('d',$datestart)==1) {
            $this->_tcpdf->SetTextColorArray($rgbText);
            $this->_tcpdf->SetFont('', '', floatval($this->_config['dates_fontsize']));
            $this->_tcpdf->Text($timeline_x-0.8, ($grid_y-3.2), $dtstart);
        }
        $curdt = $datestart;
        $dt_posx = 0;

        while($curdt < $dateend) {
            if(date('d',$curdt)==1) $curdt += 26*86400;
            while(date('d', $curdt) !=1) { $curdt += 86400; }
            $dt_posx = round($timelineWidth * ($curdt - $datestart) / ($dateend - $datestart),3);
            $strdate = date($this->_config['dateformat'], $curdt);

            if($timeline_x+$dt_posx > $width) break;
            $this->_tcpdf->Text(($timeline_x+$dt_posx-0.8), ($grid_y-3.2), $strdate);

            $this->_tcpdf->Line($timeline_x+$dt_posx,$grid_y,$timeline_x+$dt_posx, $timeline_y+$height, $lineStyle);
        }

        $kk = 0; $coords = array();

        if(count($this->_milestones) && $descWidth>0) {
            $this->_tcpdf->SetFont('', '', floatval($this->_config['taskdescr_fontsize']));
            $this->_tcpdf->MultiCell($descWidth-2,5, $this->_convertCset($this->_locStrings['milestones']), 0, 'L', 0, 1, $startx, ($milestone_y - $ms_height*0.4) );
        }

        # draw tasks on timeline
        foreach($rawdata as $itemid => $rd) {

            $posy = $timeline_y + ($step_y*$kk);
            $this->_tcpdf->Line($startx,$posy,($startx+$width), $posy, $lineStyle);
            $rdescr = ((self::$_show_taskid) ? ($itemid.':') : '') . $this->_convertCset($rd['description']);
            $this->_tcpdf->SetFont('', '', $this->_config['taskdescr_fontsize']);
            if(!empty($rd['color'])) {
                $mrgb = TCPDF_COLORS::convertHTMLColorToDec($rd['color'],$spotc);
                $this->_tcpdf->SetTextColorArray($mrgb);
            }
            else $this->_tcpdf->SetTextColorArray($rgbText);

            # task description
            if($descWidth>0) {
                $this->_tcpdf->MultiCell($descWidth-2,$step_y/2, $rdescr, 0, 'L', 0, 1, $startx, ($timeline_y + ($step_y*$kk)+0.1) );

                # working people
                if(!empty($rd['members'])) { # print working people list for the task
                    $memb = is_string($rd['members']) ? explode(',',$rd['members']) : $rd['members'];
                    foreach($memb as $k => $v) { $memb[$k] = $this->_convertCset($v); }
                    $this->_tcpdf->SetFont('', '', $this->_config['members_fontsize']);

                    $max_h = $step_y - 4.2;
                    if(!empty($rd['mcolor'])) {
                        $mrgb = TCPDF_COLORS::convertHTMLColorToDec($rd['mcolor'],$spotc);
                        $this->_tcpdf->SetTextColorArray($mrgb);
                    }
                    $this->_tcpdf->MultiCell($descWidth-2,$step_y*0.6, implode(", ",$memb), 0, 'L', 0, 1, $startx+3.0, $timeline_y + $step_y*($kk+0.3),true,0,0,true, $max_h,true);
                    $this->_tcpdf->SetTextColorArray($rgbText);
                }
            }
            # Draw task box on timeline
            $posx1 = round($timelineWidth * ($rd['datestart'] - $datestart) / ($dateend - $datestart),3);
            $w = round($timelineWidth * ($rd['dateend'] - $datestart) / ($dateend - $datestart),3) - $posx1 + $dayWidth;

            $rect_x1 = $timeline_x+$posx1; # intent
            $rect_x2 = $rect_x1+$w; # intent ending position
            $real_x1 = max($rect_x1,$timeline_x);
            $real_x2 = min($rect_x2,$max_x);
            $real_w = $real_x2 - $real_x1;

            $coords[$itemid] = array($real_x1,$posy+($step_y*0.2),$real_x2); # start x,y and end x, to draw arrows later

            if(($rect_x1>=$timeline_x && $rect_x1 <= $max_x) OR ($rect_x2>=$timeline_x && $rect_x2 <= $max_x) OR $real_x1 < $real_x2) { # <4>
#            if($real_x1 < $real_x2) { # <4> all or a part of task is inside timeline
                # draw shadows ? (shade_color)
                if($rgbShade) {
                    $this->_tcpdf->SetFillColorArray($rgbShade);
                    $shade_x = max($rect_x1 + floatval($this->_config['shade_offsetx']),$timeline_x);
                    $shade_x2 = min($rect_x2 + floatval($this->_config['shade_offsetx']), $max_x);
                    $shade_w = $shade_x2 - $shade_x;
                    $this->_tcpdf->Rect($shade_x,$posy+($step_y*0.2)+floatval($this->_config['shade_offsety']), $shade_w, ($step_y*0.57), 'F');
                }

                if(!empty($this->_milestones[$rd['datestart']]) && $rect_x1 == $real_x1) { # draw milestone at this date (if task starts inside timeline)

                    $this->_tcpdf->SetDrawColorArray($rgbGrid);
                    $this->_tcpdf->Line($rect_x1,$milestone_y,$rect_x1, $posy); # vertical line from milestone to task bar

                    $this->_tcpdf->SetFillColorArray($msFill);
                    $this->_tcpdf->SetDrawColorArray($rgbBorder);

                    $p = array($real_x1,$milestone_y+$ms_size,$real_x1-$ms_size,$milestone_y,$real_x1,$milestone_y-$ms_size,$real_x1+$ms_size,$milestone_y);
                    $this->_tcpdf->Polygon($p, 'DF');

                    $ms_title = ''; #Print milestone title
                    foreach($this->_milestones[$rd['datestart']] as $ttl) {
                        if($ttl!= '1') $ms_title .= ($ms_title ? '/':'').$ttl;
                    }
                    $ms_x = $real_x1+$ms_size;
                    $ms_w = $max_x - $ms_x;
                    if($ms_title!='' AND $ms_x < $max_x) {
                        $this->_tcpdf->SetFont('', '', floatval($this->_config['ms_fontsize']));
#                        $this->_printClipped($this->_convertCset($ms_title),$ms_x,($milestone_y-$ms_height*0.2),$ms_w,$ms_height);
                        $this->_tcpdf->MultiCell($ms_w,3, $this->_convertCset($ms_title), 0, 'L', 0, 1, $ms_x,($milestone_y-$ms_height*0.6),true,0,0,true, $ms_height,true);
#                        $this->_tcpdf->Text($real_x1+$ms_size, ($milestone_y-$ms_height*0.2), $this->_convertCset($ms_title));
                    }
                }
                $this->_tcpdf->SetDrawColorArray($rgbBorder);
                $this->_tcpdf->SetFillColorArray($rgbFill);
                $this->_tcpdf->Rect($real_x1,$posy+($step_y*0.2), $real_w, ($step_y*0.57), 'DF');

                if($rect_x1 == $real_x1) { # print task date/days only when start date inside timeline
                    $outstr = date($this->_config['dateformat2'], $rd['datestart']);
                    if($this->_config['show_taskdays']) $outstr .= ($outstr?', ':''). sprintf($this->_locStrings['days'], $rd['workdays']);
                    $this->_tcpdf->SetFont('', '', floatval($this->_config['dates_fontsize']));
                    $this->_tcpdf->Text(($timeline_x+$posx1-1.0), ($posy+($step_y*0.2)-2.5), $outstr);
                }

                if($rd['progress']<1) { # draw progress bar inside task
                    $prg_x = $rect_x1 + ($w*$rd['progress'])+0.6;
                    $prg_w = ($w*(1-$rd['progress'])-1.2);
                    $prg_x2 = min($max_x, $prg_x+$prg_w);
                    $prg_w = $prg_x2 - $prg_x;
                    $prg_rx = max($timeline_x,$prg_x); # real start pos (if tasks starts outside timeline)
                    $prg_w = $prg_x2 - $prg_rx; # real progress width
                    $this->_tcpdf->SetFillColorArray($rgbFill2);
                    $this->_tcpdf->Rect($prg_rx,$posy+($step_y*0.2)+0.5, $prg_w, ($step_y*0.57)-1.0, 'F');
                    $this->_tcpdf->SetFillColorArray($rgbFill); # back to main fill color
                    $strprc = round($rd['progress']*100,2).' %'; # text representation of completed percent
                    $this->_tcpdf->MultiCell($real_w,8, $strprc, 0, 'C', 0, 1, $real_x1, $posy+($step_y*0.3) );
                }

            } #<4>

            $kk++;
        }
        if($b_dependencies) { # draw arrows from "parent" tasks
            $rgbColor = TCPDF_COLORS::convertHTMLColorToDec($this->_config['arrow_color'], $spotc);
            $this->_tcpdf->SetDrawColorArray($rgbColor);
            $this->_tcpdf->SetFillColorArray($rgbColor);
            $this->_tcpdf->SetLineWidth(0.1); // mm

            foreach($rawdata as $itemid => $rd) {

                if(!empty($rd['dependencies'])) {
                    $darr = is_string($rd['dependencies']) ? explode(',',$rd['dependencies']) : $rd['dependencies'];
                    $b_arr = false;
                    if($rd['datestart']>=$datestart && !empty($darr[0])) foreach($darr as $taskid) {
                        if(isset($coords[$taskid][0]) && $coords[$taskid][2] < $max_x) {
                            $y_2 = round($coords[$taskid][1]+$step_y*0.3,3);
                            $x_2 = max($timeline_x, $coords[$itemid][0]+1.0);
                            if($x_2 <=$max_x) $poly = array(max($timeline_x,$coords[$taskid][2]+0.2), $y_2, $x_2-0.8, $y_2,$x_2, $y_2+0.8,$x_2, $coords[$itemid][1]-2);
                            else # task ends outside timeline, so draw only horiz.fragment of reference arrow
                                $poly = array(max($timeline_x,$coords[$taskid][2]+0.2), $y_2,  $max_x, $y_2);
                            $this->_tcpdf->Polygon($poly, '', array(),array(),false);
                            if(!$b_arr) {
                                $b_arr = TRUE;
                                $ary = $coords[$itemid][1]-2;
                                $p = array($x_2,$ary, $x_2+($step_y*0.07), $ary-$step_y*0.2,$x_2-($step_y*0.07), $ary-$step_y*0.2);
                                $this->_tcpdf->Polygon($p, 'F', array(),array(),true);
                            }
                        }
                    }
                }
            }
        }
        #restore PDF initial parameters
        $this->_restorePdfState(); # $this->_tcpdf->setFont('','',$curfontSize);
        return true;
    }
    private function _saveCurrentPdfState() {
        $this->_curstate = array(
            'fontsize' => $this->_tcpdf->getFontSize()
            ,'fontfamily' => $this->_tcpdf->getFontFamily()
##            ,'textcolor' => $this->_tcpdf->????
        );
    }
    private function _restorePdfState() {
        $this->_tcpdf->setFont($this->_curstate['fontfamily'],'',$this->_curstate['fontsize']);
        $this->_tcpdf->SetTextColor(0,0,0);
    }
    private function _printClipped($text, $x, $y, $w, $h) {
        // Start clipping.
        $this->_tcpdf->StartTransform();
        $this->_tcpdf->Rect($x, $y, $w, $h, 'CNZ');
        $this->_tcpdf->writeHTMLCell($w, $h, $x, $y, $text);
        $this->_tcpdf->StopTransform();
    }
}

/**
* create plugin class derived from PfPdfPlugin, for using in PrintFormPdf
*/
if(class_exists('PfPdfPlugin')) {

    class PfPdf_Gantt extends PfPdfPlugin {

        private $gantt_obj = null;

        public function __construct($pdfobj, $config=null, $x=0,$y=0,$w=0,$h=0) {
            $this->gantt_obj = new PdfGantt($pdfobj,$config,$x,$y,$w,$h);
        }
        public function Render($data) {

            $result = $this->gantt_obj->Render($data);
            $this->_error_message = $this->gantt_obj->getErrorMessage();
            $this->gantt_obj = null;
            return $result;

        }
    }

}
