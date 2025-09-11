@include($dynamicTemplate)
@extends($layout . '.master')
@component('admin.' . $section)
<?php
view($templateName);
View::make('template.' . $type);