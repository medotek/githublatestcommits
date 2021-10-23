{if $commits}
    <div class="container">
        <div id="githublatestcommits">
            <h3>Latest commits from {$user}'s repo ({$repo})</h3>
            <div class="row">
                <div class="col-6 text-center">User's commit info</div>
                <div class="col-6 text-center">Commit message</div>
                {foreach from=$commits item=element}
                    <div class="commit">
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
    </div>
{/if}
