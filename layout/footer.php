
</div>

<div id="footerspacer"></div>
</div>

<div id="footer">
<ul>
    <li><a href="http://validator.w3.org/check?uri=referer">Valid XHTML</a> |</li>
    <li><a href="http://jigsaw.w3.org/css-validator/check/referer">Valid CSS</a></li>
</ul>
<span>
    Diese Seite ist in folgenden Sprachen verfügbar:
    <? foreach(language_list_languages() as $code => $lang):
        if(@$i++ != 0) echo("|") ?>
        <a href="?a=lang&l=<?= $code ?>"><?= $lang ?></a>
    <? endforeach; ?>
</span>
</div>

</body>
</html>
