## Laravel

### IxDFCodingStandard.Laravel.DisallowGuardedAttribute
Do not allow using [Mass Assignment](https://laravel.com/docs/master/eloquent#mass-assignment).


### IxDFCodingStandard.Laravel.NonExistingBladeTemplate

Detects missing Blade templates in `@include`, `@includeFirst`, `@componentFirst`, `@each`, and `view()` calls.

```xml
<rule ref="IxDFCodingStandard.Laravel.NonExistingBladeTemplate">
    <properties>
        <!-- the same as you config('view.paths') output -->
        <property name="viewPaths" type="array">
            <element value="resources/views"/>
        </property>
        <!-- for cases like @include('package::someView') -->
        <property name="viewNamespaces" type="array">
            <element key="package" value="resources/views/vendor/package"/>
        </property>
    </properties>
</rule>
```

### IxDFCodingStandard.Laravel.RequireCustomAbortMessage
Force using custom exception messages when use `abort`, `abort_if` and `abort_unless`.
