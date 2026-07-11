# Custom PHP_CodeSniffer sniffs

Sniffs shipped by `IxDFCodingStandard` on top of the Generic, PSR1, Squiz and Slevomat rulesets.

Three of them are enabled automatically by `<rule ref="IxDFCodingStandard"/>`. The rest are opt-in: reference them explicitly in your `phpcs.xml` (usually with their required properties).

| Sniff                                                                                | Enabled | What it does                                               |
|--------------------------------------------------------------------------------------|---------|------------------------------------------------------------|
| [Classes.ForbidDirectClassInheritance](#classesforbiddirectclassinheritance)         | opt-in  | Forbid extending a configured list of classes.             |
| [Classes.ForbidMethodDeclaration](#classesforbidmethoddeclaration)                   | opt-in  | Forbid declaring configured methods.                       |
| [Files.BemCasedFilename](#filesbemcasedfilename)                                     | opt-in  | Enforce BEM notation for Blade file names.                 |
| [Functions.ForbiddenFunctions](#functionsforbiddenfunctions)                         | default | Forbid configured functions and suggest replacements.      |
| [Functions.MissingOptionalArgument](#functionsmissingoptionalargument)               | opt-in  | Require a minimum number of arguments in configured calls. |
| [Laravel.DisallowGuardedAttribute](#laraveldisallowguardedattribute)                 | opt-in  | Forbid the Eloquent `$guarded` property (mass assignment). |
| [Laravel.NonExistingBladeTemplate](#laravelnonexistingbladetemplate)                 | opt-in  | Detect references to missing Blade templates.              |
| [Laravel.RequireCustomAbortMessage](#laravelrequirecustomabortmessage)               | opt-in  | Require a message in `abort`, `abort_if`, `abort_unless`.  |
| [NamingConventions.CamelCaseRouteName](#namingconventionscamelcaseroutename)         | default | Forbid camelCased Laravel route names.                     |
| [NamingConventions.MeaningfulVariableName](#namingconventionsmeaningfulvariablename) | default | Forbid uninformative variable names.                       |

## Classes

### Classes.ForbidDirectClassInheritance
Forbid a given list of classes from being inherited directly.

```xml
<rule ref="IxDFCodingStandard.Classes.ForbidDirectClassInheritance">
    <properties>
        <property name="forbiddenClasses" type="array">
            <element value="\Illuminate\Database\Eloquent\Model"/>
        </property>
    </properties>
</rule>
```

### Classes.ForbidMethodDeclaration
Forbid declaration of given methods.

```xml
<rule ref="IxDFCodingStandard.Classes.ForbidMethodDeclaration">
    <properties>
        <property name="forbiddenMethods" type="array">
            <element key="\App\Notifications\Notification::shouldSend" value="\App\Notifications\Support\ShouldCheckConditionsBeforeSendingOut::shouldBeSent"/>
        </property>
    </properties>
</rule>
```

## Files

### Files.BemCasedFilename
Ensure Blade files are named using [BEM notation](https://getbem.com/introduction/).

```xml
<rule ref="IxDFCodingStandard.Files.BemCasedFilename">
    <include-pattern>*/resources/views/*</include-pattern>
</rule>
```

## Functions

### Functions.ForbiddenFunctions
Forbid configured functions and propose alternatives. Enabled by default with a Laravel-helpers blocklist (`auth`, `app`, `request`, `dd`, and more); see [`ruleset.xml`](/IxDFCodingStandard/ruleset.xml) for the full list. Extend or replace it via the `forbiddenFunctions` property.

### Functions.MissingOptionalArgument
Require that configured function or static-method calls pass at least a given number of arguments, even when later arguments are optional in the signature. Useful for enforcing an explicit argument that defaults to an unwanted value (for example a locale, timezone, or strict flag).

```xml
<rule ref="IxDFCodingStandard.Functions.MissingOptionalArgument">
    <properties>
        <property name="functions" type="array">
            <!-- call => minimum number of arguments expected -->
            <element key="json_encode" value="2"/>
        </property>
        <property name="staticMethods" type="array">
            <element key="\Carbon\Carbon::createFromFormat" value="3"/>
        </property>
    </properties>
</rule>
```

## Laravel

### Laravel.DisallowGuardedAttribute
Forbid the Eloquent `$guarded` property, forcing explicit [mass assignment](https://laravel.com/docs/master/eloquent#mass-assignment) via `$fillable`.

```xml
<rule ref="IxDFCodingStandard.Laravel.DisallowGuardedAttribute">
    <include-pattern>*/app/Models/*</include-pattern>
</rule>
```

### Laravel.NonExistingBladeTemplate
Detect missing Blade templates in `@include`, `@includeFirst`, `@componentFirst`, `@each`, and `view()` calls.

```xml
<rule ref="IxDFCodingStandard.Laravel.NonExistingBladeTemplate">
    <properties>
        <!-- the same as your config('view.paths') output -->
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

### Laravel.RequireCustomAbortMessage
Force a custom message when using `abort`, `abort_if`, and `abort_unless`.

```xml
<rule ref="IxDFCodingStandard.Laravel.RequireCustomAbortMessage"/>
```

## Naming conventions

### NamingConventions.CamelCaseRouteName
Ensure a Laravel route name is not camelCased (enabled by default, scoped to `*/routes/*`).

### NamingConventions.MeaningfulVariableName
Forbid uninformative variable names (enabled by default). The blocklist of names, each paired with a hint, lives in `ruleset.xml` and can be overridden via the `forbiddenNames` property.
