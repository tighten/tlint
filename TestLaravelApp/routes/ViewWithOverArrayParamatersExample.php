<?php

Route::get('test', function () {
    // \Tighten\Linters\ViewWithOverArrayParamaters::class
    return view('test', ['test' => 'test']);
});
