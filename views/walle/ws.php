<?php
/**
 * @var yii\web\View $this
 */
use yii\helpers\Url;
$this->title = yii::t('walle', 'md5 title');
?>
<div id="output">
    output...
</div>

<script src="//cdn.jsdelivr.net/sockjs/1.0.3/sockjs.min.js"></script>


<script type="text/javascript">
    $(function() {
        var wsUri ="ws://127.0.0.1:2346/";
        var output;
        function init() {
            output = document.getElementById("output");
            testWebSocket();
        }
        var stringToObject = function(json) {
            return eval("(" + json + ")");
        }
        function testWebSocket() {
            websocket = new WebSocket(wsUri);
            websocket.onopen = function(evt) {
                onOpen(evt)
            };
            websocket.onclose = function(evt) {
                onClose(evt)
            };
            websocket.onmessage = function(evt) {
                onMessage(evt)
            };
            websocket.onerror = function(evt) {
                onError(evt)
            };
        }
        function onOpen(evt) {
            writeToScreen("CONNECTED");
//            doSend("106");
            doSend("pwd");
//            doSend("ls");
//            doSend("sleep 10");
//            doSend("git status");
        }
        function onClose(evt) {
            writeToScreen("DISCONNECTED");
        }
        function onMessage(evt) {
            var data = evt.data;
            writeToScreen('<span style="color: blue;">RESPONSE: ' + data + '</span>');
//            console.log(data);
            writeToScreen('<span style="color: blue;">TYPE: ' + typeof(data) + '</span>');
            data = stringToObject(data)
            writeToScreen('<span style="color: blue;">TYPE: ' + typeof(data) + '</span>');
            writeToScreen('<span style="color: blue;">RESPONSE: ' + data + '</span>');
            for (i in data) {
                writeToScreen('<span style="color: blue;">RESPONSE: ' + data[i] + '</span>');
            }
            // websocket.close();
        }
        function onError(evt) {
            writeToScreen('<span style="color: red;">ERROR:</span> '+ data);
        }
        function doSend(message) {
            writeToScreen("SENT: " + message);
            websocket.send(message);
        }
        function writeToScreen(message) {
            var pre = document.createElement("p");
            pre.style.wordWrap = "break-word";
            pre.innerHTML = message;
            output.appendChild(pre);
        }
        window.addEventListener("load", init, false);
    })
</script>
