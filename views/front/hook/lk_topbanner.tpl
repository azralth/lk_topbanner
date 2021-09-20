{if $lk_topbanner_social}
    <div class="lk-topbanner">
        <div class="text">
            {$lk_topbanner_text}
        </div>
        {if !empty($lk_topbanner_social)}
            <div class="socials-link">
                <ul>
                    {foreach from=$lk_topbanner_social key=k item=fields}
                        {if $k != 'top_banner_text'}
                            <li><a class="{$k} icon_{$k}" href="{$fields}" title="{$k}" target="_blank"><i class="fab fa-{$k}"></i></a></li>
                        {/if}
                    {/foreach}
                </ul>
            </div>
        </div>
    {/if}
{/if}