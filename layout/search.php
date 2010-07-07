<ul id="timeline">
    <li class="prev">
    </li>

<? foreach($timeline as $entry): ?>
    <li class="<?= $entry['type'] ?>">
<? if($entry['type'] != TIMELINE_DISABLED): ?>
        <a href="?a=search&t=<?= $entry['date'] ?>">
<? endif; ?>
        <?= search_get_human_readable_date($entry['date']) ?>
<? if($entry['type'] != TIMELINE_DISABLED): ?>
        </a>
<? endif; ?>
    </li>
<? endforeach; ?>

    <li class="next">
    </li>
</ul>
<hr class="clear"/>
<form action="?a=search" method="get" id="search">
    <input name="a" type="hidden" value="search" />
    <input name="t" type="hidden" value="<?= $selected ?>" />
    <input name="q" type="text" size="20" value="<?= $query ?>" />
    <input name="e" type="text" size="1" maxlength="2" value="<?= $until ?>" />
    <label>Wochen zurück</label>

    <input type="submit" value="Suchen" />
</form>
<?php
    foreach($result as $file) {
        echo $file['Filename'].' - '.$file['FileID'].'<br>';
    }
    echo count($result).' files found.';
?>
