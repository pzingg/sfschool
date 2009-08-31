
Dear {$parent_1_Name}{if $parent_2_Name} and {$parent_2_Name}{/if}:

You recently made the following changes to your child's extended care schedule:

{if ! empty($classSignedUpFor) }

You signed up for the following classes for {$childName}
{foreach from=$classSignedUpFor item=class}
    {$class.day} : {$class.title}
{/foreach}

{/if}

{if ! empty($classCancelled) }

You cancelled the following classes for {$childName}
{foreach from=$classCancelled item=class}
    {$class.day} : {$class.title}
{/foreach}

{/if}

{if $classEnrolled}

{$childName} is currently enrolled in the following extended care activities for {$term}:
{foreach from=$classEnrolled item=class}
     {$class.title}
{/foreach}

{/if}

You can make changes to this schedule online. If you have any questions, please contact {$extendedCareCoordinatorName} at {$extendedCareCoordinatorEmail}

Thank you

{$extendedCareCoordinatorName}
The San Francisco School
