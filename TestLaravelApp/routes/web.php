<?php

Route::get('test', function () {
    return view('test', ['test' => 'test']);
});
