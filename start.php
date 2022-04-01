<?php

$htaccess = "RewriteEngine On\nRewriteCond %{REQUEST_URI} !\.(woff2|css|js|json|png|jpg|gif)$ [NC]\nRewriteRule ^(.*)$ index.php [L,QSA]";
