export function formatDate(value: string): string {
  return new Date(value).toLocaleString('it-IT', {
    dateStyle: 'medium',
    timeStyle: 'short'
  });
}
