<?php
// Display session messages.
$style = "my-6 font-bold text-white text-center p-2";
handleSessionMessages("success", true, "bg-green-300 {$style}");
handleSessionMessages("fail", true, "bg-red-300 {$style}");
handleSessionMessages("info", true, "bg-blue-400 {$style}");
handleSessionMessages("verification_status", true, "bg-orange-400 {$style}");
