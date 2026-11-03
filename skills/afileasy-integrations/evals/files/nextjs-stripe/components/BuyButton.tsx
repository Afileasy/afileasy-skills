'use client';

import { useState } from 'react';

export default function BuyButton({ priceId }: { priceId: string }) {
  const [loading, setLoading] = useState(false);

  async function handleClick() {
    setLoading(true);
    const res = await fetch('/api/checkout', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ priceId }),
    });
    const { url } = await res.json();
    window.location.href = url;
  }

  return (
    <button onClick={handleClick} disabled={loading}>
      {loading ? 'Redirecionando…' : 'Comprar agora'}
    </button>
  );
}
