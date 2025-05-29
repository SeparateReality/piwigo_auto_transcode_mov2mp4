<div class="titrePage">
  <h2>CXO MOV Transcoder</h2>
</div>

{* flash messages from main.php *}
{if isset($page.infos)}
  {foreach from=$page.infos item=msg}
    <div class="infos">{$msg}</div>
  {/foreach}
{/if}

<form method="post">
  <p>
    Remaining MOV files: <strong>{$MOV_LEFT}</strong>
  </p>

  <p style="display:flex;justify-content:center; gap:1rem;align-items:center;">
    {* Bulk convert *}
    <input type="submit"
           name="bulk"
           value="Convert legacy MOV files"
           class="button-primary"
           {if $MOV_LEFT==0}disabled{/if} />

    {* Clear log *}
    <input type="submit"
           name="clear_log"
           value="Clear Log"
           class="button" />
  </p>
</form>


<h3>Log (last 5 kB, auto-refresh every 5 s)</h3>
<pre id="cxo-log"
     style="max-height:300px;overflow:auto;border:1px solid #ccc;
            padding:6px;background:#fafafa;"></pre>

<script>
  function loadLog () {
    fetch('{$ROOT_URL}admin.php?page=plugin-cxoAutoTranscode&ajaxlog=1')
      .then(r => r.text())
      .then(t => { document.getElementById('cxo-log').textContent = t; });
  }
  loadLog();                     // initial load
  setInterval(loadLog, 5000);    // refresh every 5 s
</script>