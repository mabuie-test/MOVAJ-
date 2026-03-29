async function fetchQuote(formId) {
  const form = document.getElementById(formId);
  const data = new FormData(form);
  const output = document.getElementById('quote-output');

  try {
    const response = await fetch('/merchant/orders/quote', { method: 'POST', body: data });
    const json = await response.json();
    output.textContent = JSON.stringify(json, null, 2);
  } catch (e) {
    output.textContent = `Erro ao cotar rota: ${e.message}`;
  }
}
