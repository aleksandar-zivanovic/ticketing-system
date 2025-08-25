<?php
// Display session messages.
$style = "my-6 font-bold text-white text-center p-2";
handleSessionMessages("success", true, "bg-green-400 {$style}");
handleSessionMessages("fail", true, "bg-red-400 {$style}");
handleSessionMessages("info", true, "bg-blue-400 {$style}");
