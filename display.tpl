{if $page_name == 'index'}
  <div id="ww_popup">
    <div id="dialog">
      {if $popup_title}
        <h2>{$popup_title}</h2>
      {/if}
      {$popup_body}
      <a title="Close" id="close" href="#">X</a>
    </div>
    <div id="mask"></div>
  </div>
{/if}
