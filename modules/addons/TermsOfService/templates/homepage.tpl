<div class="card">
    <div class="card-body">
        {foreach from=$terms item=term}
        <h4>{$term.title}</h4>
        <p>{$term.contents}</p>
        {/foreach}
    </div>
</div>