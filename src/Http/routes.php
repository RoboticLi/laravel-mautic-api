<?php

/*
|--------------------------------------------------------------------------
| Mautic Application Register
|--------------------------------------------------------------------------
|
*/

Route::get( "application/register{id}", "MauticController@initiateApplication" );
