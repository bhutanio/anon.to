<?php

Route::get('/{key}', 'RedirectController@redirect')->where('key', '[A-Za-z0-9]{6}');