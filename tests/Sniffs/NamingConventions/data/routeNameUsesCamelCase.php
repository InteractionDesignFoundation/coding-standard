<?php declare(strict_types=1);

route('contactLists.index');
route_relative('contactLists.index');

$resourceName = 'contactLists';
route("myPrivateProfile.$resourceName.show");
