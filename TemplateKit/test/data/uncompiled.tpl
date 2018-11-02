<?xml version="1.0" encoding="UTF-8"?>

Hi, {* comment section *} this is
{if $content == "Hello"}
    {$variable} content
    {literal}literal with { things } {/literal} and so
    {mytag required dingen="zaken" other=$variable}Tag content{/mytag}
{else}
    else content
{/if}
with extra content {extra/}, some <?php maliciuous_php_code(); ?> code and a tail and an iteration:
{for $index,$number in 1..<10}
    {if $index > 0}, {/if}Number: {$number}
{/for}
so there you go!
