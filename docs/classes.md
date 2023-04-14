## Classes

### IxDFCodingStandard.Classes.ForbidDirectClassInheritance
Forbid a given list classes from using inherited directly.

### IxDFCodingStandard.Classes.ForbidMethodDeclaration
Forbid declaration of given methods. Example:
```xml
<rule ref="IxDFCodingStandard.Classes.ForbidMethodDeclaration">
    <properties>
        <property name="forbiddenMethods" type="array">
            <element key="\App\Notifications\Notification::shouldSend" value="\App\Notifications\Support\ShouldCheckConditionsBeforeSendingOut::shouldBeSent"/>
        </property>
    </properties>
</rule>
```
