<script>
  let count = 0;

  function increment() {
    count += 1;
  }
</script>

<div>
  <button on:click={increment}>
    count is {count}
  </button>
  <p>Edit <code>src/lib/Counter.svelte</code> to test HMR</p>
</div>

<style>
  div {
    text-align: center;
  }

  button {
    padding: 0.6em 1.2em;
    font-size: 1em;
    font-weight: 600;
    cursor: pointer;
    background-color: #f9f9f9;
    border: 2px solid #646cff;
    border-radius: 8px;
    color: #646cff;
  }

  button:hover {
    border-color: #6e9add;
    color: #6e9add;
  }
</style>