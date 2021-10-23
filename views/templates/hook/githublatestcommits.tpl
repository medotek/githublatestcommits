{if $commits}
    {if $commits.error !== "error"}
        <div id="githublatestcommits">
            <h3>{$number} last commits from {$user}'s repo ({$repo})</h3>
            <div class="row">
                <div class="column-title col-6">User's commit info</div>
                <div class="column-title col-6">Commit message</div>

                {foreach from=$commits item=element}
                    <div class="commit">
                        <div class="row">
                            <div class="align-self-center info col-6">
                                <span><b>{$element.commit.author.name}</b></span>
                                <span>{$element.commit.author.email}</span>
                                <span>{$element.commit.author.date|date_format}</span>
                            </div>
                            <div class="message col-6">
                                <p>{$element.commit.message}</p>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    {else}
        <div id="githublatestcommits-error">
            <span>No commits are found. The user and/or the repository doesn't exist or (the repository) is not public.</span>
        </div>
    {/if}
{/if}
