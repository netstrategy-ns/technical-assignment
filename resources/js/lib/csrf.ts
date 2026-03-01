export function csrfToken(): string {
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  if (match) {
    return decodeURIComponent(match[1]);
  }

  const token = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
  return token?.content ?? '';
}
