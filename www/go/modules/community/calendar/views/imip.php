<style>
    a {text-decoration: none !important;}
    .bord {
        border: 1px solid #e0e0e0;
        border-radius:8px;
        color:#01040a;
        background-color:#ffffff;
        overflow:hidden;
    }
    .card {
        padding-top: 32px;
        font-family:-apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
        border:1px solid #e0e0e0;
        width: 680px;
        font-size:14px;
        margin: 24px auto;
    }
    .foot { margin-top: 32px; padding: 8px 0; border-top: 1px solid #e0e0e0; background-color: #fafafa; }
    a.bord {
        display:inline-block;
        min-width:123px;
        text-align:center;
        font-weight:600;
        line-height:28px;
        margin-left:13px;
    }
    a.bord:hover,a.bord.active { background-color:#DBE3F0;}
    span { color: #70757a; }
    p { margin-bottom: 24px;}
    h1 { display: inline-block; overflow-wrap: break-word;
        margin-top:0;
        font-size: 36px; font-weight: 500;
    }
    h3 { font-size: 17px; font-weight: 300; color: #5c5c5c; margin:0; }
    h4 { color: #1652a1; font-size: 18px; font-weight: 400; margin:0;}
    h5 { margin-bottom:0; margin-top:24px; line-height: 18px; color:#01040a; }
    .cal {
        background-color: #fafafa;
        border-radius: 4px;
        margin-left:32px !important;
        float: left;
        width: 46px !important;
        text-align:center;
        font-size: 24px;
        line-height: 28px;
    }
    .cal div {
        color:white;
        background-color: #1652a1;
        font-size: 10px;
        line-height: 18px;
    }
    .center { margin-left: 112px; width: 456px;}
    @media only screen and (max-width: 628px) {
        .card {
            width: 360px !important;
        }
        .center {
            width: 312px !important;
            margin-left: 24px !important;
        }
        h4 {
            float:right;
            margin-top:8px;
            margin-bottom:32px;
        }
        a.bord {
            margin-top:5px;  !important
            margin-bottom: 5px !important;
            min-width: 312px !important;
            margin-left: 24px !important;
            line-height:40px;
        }
    }
</style><?php /**
 * @var $event \go\modules\community\calendar\model\CalendarEvent
 * @var $title string
 * @var $method string
 * @var $url string
 * @var $status string[] could contain classname for the participant's current status
 * @var $participant mixed */
$icon = [
   'accepted'=>'check_circle',
   'tentative'=>'help',
   'declined'=>'block',
   'needs-action'=>'schedule']
?>
<div class="card bord">

    <div class="cal bord center">
        <div><?=go()->t('short_months')[$event->start->format('n')]?></div><?=$event->start->format('d')?>
    </div>
    <div class="center">
        <h4 <?=$method==='CANCEL'?'style="color:red;"':''?>><?=$title?></h4>
        <h1  <?=$method==='CANCEL'?'style="text-decoration: line-through;"':''?>><?=htmlentities($event->title)?></h1>
        <div>
            <?php $timeLines = $event->humanReadableDate();?>
            <h3><?=$timeLines[0];?></h3>
            <h3><?=$timeLines[1]; ?><span style="margin-left: 16px; font-size: .7em;"><?=!$event->showWithoutTime ? $event->timeZone:''?></span></h3>

            <?= !empty($event->description) ? '<p>'.htmlentities($event->description).'</p>':''?>

            <?php if($event->participants):?>
            <h5><?=go()->t('Participants', 'community','calendar')?></h5>
            <?php foreach($event->participants as $part):  ?>
                <div><?=$method==="PAGE"?'<i class="icon">'.$icon[$part->participationStatus].'</i> ':''; ?><?=$part->name ? '<a href="mailto:'.htmlentities($part->email).'" target="_blank" rel="noopener noreferrer">'.htmlentities($part->name).'</a>' :
                      '<a href="mailto:'.htmlentities($part->email).'" target="_blank" rel="noopener noreferrer">'.htmlentities($part->email).'</a>'?>
                    <?php if($part->isOwner()) echo '&bull; <span>'.go()->t('Organizer').'</span>'; ?>
                    <?php if($part->email == $participant->email) echo '&bull; <span>'.go()->t('You').'</span>'; ?>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
    <div class="foot"><?php if($method!=='CANCEL'): ?>
        <a class="bord <?=$status['accepted']??''?>"  target="<?=$method==='PAGE'?'':'_blank'?>" href="<?=$url?>?reply=accepted" style="margin-left:112px; margin-top:0;" ><?=go()->t('Akkoord')?></a>
        <a class="bord <?=$status['tentative']??''?>" target="<?=$method==='PAGE'?'':'_blank'?>" href="<?=$url?>?reply=tentative"><?=go()->t('Misschien')?></a>
        <a class="bord <?=$status['declined']??''?>" target="<?=$method==='PAGE'?'':'_blank'?>" href="<?=$url?>?reply=declined"><?=go()->t('Afwijzen')?></a>
		<?php endif; ?>
    </div>

</div>