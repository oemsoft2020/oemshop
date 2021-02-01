<?php

use think\facade\Route;

Route::get('g/:name','app\\common\\service\\BaseService@version');