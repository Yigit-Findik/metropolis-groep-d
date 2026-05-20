document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('filters');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const params = new URLSearchParams(new FormData(form));
    const url = `${window.location.pathname}?${params.toString()}`;

    try {
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const text = await res.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(text, 'text/html');
      const newTbody = doc.querySelector('#audit-rows');
      if (newTbody) {
        const tbody = document.querySelector('#audit-rows');
        tbody.innerHTML = newTbody.innerHTML;
        history.replaceState({}, '', url);
      } else {
        // fallback to a normal navigation
        window.location.href = url;
      }
    } catch (err) {
      console.error(err);
      window.location.href = url;
    }
  });
});
