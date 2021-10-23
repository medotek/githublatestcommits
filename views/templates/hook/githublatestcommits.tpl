{if $commits}
    <div id="githublatestcommits">
        {$commits|@var_dump}
{*        <ul>*}
{*            {foreach from=$commits item=commit}*}
{*                <li>{$commit}</li>*}
{*            {/foreach}*}
{*        </ul>*}
    </div>
{/if}
