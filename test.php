<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Unbenanntes Dokument</title>
<style type="text/css">
<!--
body {
	font: 100%/1.4 Verdana, Arial, Helvetica, sans-serif;
	background-color: #4E5869;
	margin: 0;
	padding: 0;
	color: #000;
}

/* ~~ Element-/Tag-Selektoren ~~ */
ul, ol, dl { /* Aufgrund von Abweichungen zwischen verschiedenen Browsern empfiehlt es sich, die Auffüllung und den Rand in Listen auf 0 einzustellen. Zu Konsistenzzwecken können Sie die gewünschten Werte entweder hier oder in den enthaltenen Listenelementen (LI, DT, DD) eingeben. Beachten Sie, dass die hier eingegebenen Werte hierarchisch auf die .nav-Liste angewendet werden, sofern Sie keinen spezifischeren Selektor festlegen. */
	padding: 0;
	margin: 0;
}
h1, h2, h3, h4, h5, h6, p {
	margin-top: 0;	 /* Durch Verschieben des oberen Rands wird das Problem behoben, dass Ränder aus dem zugehörigen div-Tag geraten können. Der übrig gebliebene untere Rand hält ihn getrennt von allen folgenden Elementen. */
	padding-right: 15px;
	padding-left: 15px; /* Durch Hinzufügen der Auffüllung zu den Seiten der Elemente innerhalb der div-Tags anstelle der div-Tags selbst entfallen jegliche Box-Modell-Berechnungen. Alternativ kann auch ein verschachteltes div-Tag mit seitlicher Auffüllung verwendet werden. */
}
a img { /* Dieser Selektor entfernt den standardmäßigen blauen Rahmen, der in einigen Browsern um ein Bild angezeigt wird, wenn es von einem Hyperlink umschlossen ist. */
	border: none;
}

/* ~~ Die Reihenfolge der Stildefinitionen für die Hyperlinks der Site, einschließlich der Gruppe der Selektoren zum Erzeugen des Hover-Effekts, muss erhalten bleiben. ~~ */
a:link {
	color:#414958;
	text-decoration: underline; /* Sofern Ihre Hyperlinks nicht besonders hervorgehoben werden sollen, empfiehlt es sich, zur schnellen visuellen Erkennung Unterstreichungen zu verwenden. */
}
a:visited {
	color: #4E5869;
	text-decoration: underline;
}
a:hover, a:active, a:focus { /* Durch diese Gruppe von Selektoren wird bei Verwendung der Tastatur der gleiche Hover-Effekt wie beim Verwenden der Maus erzielt. */
	text-decoration: none;
}

/* ~~ Dieser Container umschließt alle anderen div-Tags und weist ihnen ihre als Prozentwert definierte Breite zu. ~~ */
.container {
	width: 80%;
	max-width: 1260px;/* Es empfiehlt sich die Eingabe einer maximalen Breite (Eigenschaft max-width), damit dieses Layout auf einem großen Bildschirm nicht zu breit angezeigt wird. Dadurch bleibt die Zeilenlänge besser lesbar. IE6 berücksichtigt diese Deklaration nicht. */
	min-width: 780px;/* Es empfiehlt sich die Eingabe einer minimalen Breite (Eigenschaft min-width), damit dieses Layout nicht zu schmal angezeigt wird. Dadurch bleibt die Zeilenlänge in den seitlichen Spalten besser lesbar. IE6 berücksichtigt diese Deklaration nicht. */
	background-color: #FFF;
	margin: 0 auto; /* Der mit der Breite gekoppelte automatische Wert an den Seiten zentriert das Layout. Er ist nicht erforderlich, wenn Sie die Breite von .container auf 100 Prozent setzen. */
}

/* ~~ Für die Kopfzeile wird keine Breite angegeben. Sie erstreckt sich über die gesamte Breite des Layouts. Sie enthält einen Bild-Platzhalter, der durch Ihr eigenes, mit Hyperlink versehenes Logo ersetzt werden sollte. ~~ */
.header {
	background-color: #6F7D94;
}

/* ~~ Dies sind die Layoutinformationen. ~~ 

1) Eine Auffüllung wird nur oben und/oder unten im div-Tag positioniert. Die Elemente innerhalb dieses div-Tags verfügen über eine seitliche Auffüllung. Dadurch müssen Sie keine Box-Modell-Berechnungen durchführen. Zu beachten: Wenn Sie dem div-Tag eine seitliche Auffüllung oder einen Rahmen hinzufügen, werden diese zu der festgelegten Breite addiert und ergeben die *gesamte* Breite. Sie können auch die Auffüllung für das Element im div-Tag entfernen und ein zweites div-Tag ohne Breite und mit der gewünschten Auffüllung im ersten div-Tag einfügen.

*/
.content {
	padding: 10px 0;
}

/* ~~ Dieser gruppierte Selektor gibt die Listen im .content-Bereich an. ~~ */
.content ul, .content ol { 
	padding: 0 15px 15px 40px; /* Diese Auffüllung setzt die rechte Auffüllung in der obigen Regel für Überschriften und Absätze fort. Die Auffüllung wurde unten für den Abstand zwischen anderen Elementen in den Listen und links für den Einzug platziert. Sie können die Werte nach Bedarf ändern. */
}

/* ~~ Fußzeile ~~ */
.footer {
	padding: 10px 0;
	background-color: #6F7D94;
}

/* ~~ Verschiedene float/clear-Klassen ~~ */
.fltrt {  /* Mit dieser Klasse können Sie ein Element auf der Seite nach rechts fließen lassen. Das fließende Element muss vor dem Element stehen, neben dem es auf der Seite erscheinen soll. */
	float: right;
	margin-left: 8px;
}
.fltlft { /* Mit dieser Klasse können Sie ein Element auf der Seite nach links fließen lassen. Das fließende Element muss vor dem Element stehen, neben dem es auf der Seite erscheinen soll. */
	float: left;
	margin-right: 8px;
}
.clearfloat { /* Diese Klasse kann in einem <br />-Tag oder leeren div-Tag als letztes Element nach dem letzten fließenden div-Tag (im #container) platziert werden, wenn #footer entfernt oder aus dem #container herausgenommen wird. */
	clear:both;
	height:0;
	font-size: 1px;
	line-height: 0px;
}
-->
</style></head>

<body>

<div class="container">
  <div class="header"><a href="#"><img src="" alt="Hier Logo einfügen" name="Insert_logo" width="20%" height="90" id="Insert_logo" style="background-color: #8090AB; display:block;" /></a> 
    <!-- end .header --></div>
  <div class="content">
    <h1>Anweisungen</h1>
    <p>Beachten Sie, dass der CSS-Code für diese Layouts mit vielen Kommentaren versehen ist. Wenn Sie vor allem in der Entwurfsansicht arbeiten, werfen Sie einen Blick auf den Code, um Tipps zum Verwenden von CSS für die fließenden Layouts zu erhalten. Sie können diese Kommentare vor dem Veröffentlichen Ihrer Site löschen. Weitere Informationen zu den in diesen CSS-Layouts verwendeten Methoden finden Sie in diesem Artikel im Adobe Developer Center - <a href="http://www.adobe.com/go/adc_css_layouts">http://www.adobe.com/go/adc_css_layouts</a>.</p>
    <h2>Layout</h2>
    <p>Da es sich um ein einspaltiges Layout handelt, ist der .content nicht fließend. </p>
    <h3>Ersetzen des Logos</h3>
    <p>In diesem Layout wurde ein Bild-Platzhalter im .header verwendet, wo in der Regel ein Logo platziert wird. Es empfiehlt sich, dass Sie den Platzhalter entfernen und durch Ihr eigenes, als Hyperlink eingebundenes Logo ersetzen. </p>
    <p> Wenn Sie mithilfe des Eigenschafteninspektors im Feld "Quelle" zum Bild Ihres Logos navigieren (anstelle den Platzhalter zu entfernen und zu ersetzen), müssen Sie die Inline-Stile für Hintergrund und Anzeige entfernen. Diese Inline-Stile werden lediglich zum Anzeigen des Bild-Platzhalters in Browsern zu Demonstrationszwecken verwendet. </p>
    <p>Stellen Sie zum Entfernen der Inline-Stile sicher, dass das Bedienfeld "CSS-Stile" auf "Aktuell" gesetzt ist. Wählen Sie das Bild aus, klicken Sie im Bereich "Eigenschaften" des Bedienfelds "CSS-Stile" mit der rechten Maustaste und löschen Sie die Anzeige- und Hintergrundeigenschaften. (Sie können die Inline-Stile für das Bild oder den Platzhalter natürlich auch direkt im Code löschen.)</p>
    <!-- end .content --></div>
  <div class="footer">
    <p><?php print("abc")?></p>
    <!-- end .footer --></div>
  <!-- end .container --></div>
</body>
</html>