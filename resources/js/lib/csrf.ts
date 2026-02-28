export function csrfToken(): string {
  const token = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
  return token?.content ?? '';
}
