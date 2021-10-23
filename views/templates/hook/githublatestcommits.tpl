{if $commits}
    {if 'error'|array_key_exists:$commits}
        <div id="error">
            <p>No commits are found, maybe the user or the repo doesn't exist or is not public</p>
        </div>
    {else}
        <div class="container">
            <div id="githublatestcommits">
                <h3>Latest commits from {$user}'s repo ({$repo})</h3>
                {foreach from=$commits item=element}
                    <div class="commit row">
                        <div class="align-self-center info col-6">
                            <span><b>{$element.commit.author.name}</b></span>
                            <span>{$element.commit.author.email}</span>
                            <span>{$element.commit.author.date|date_format}</span>
                        </div>
                        <div class="message col-6">
                            <p>{$element.commit.message}</p>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    {/if}
{/if}
