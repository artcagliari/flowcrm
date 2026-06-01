export function applyCompanyTheme(primaryColor = '#4F8CFF') {
  if (!/^#[0-9A-Fa-f]{6}$/.test(primaryColor)) return;
  const root = document.documentElement;
  root.style.setProperty('--primary', primaryColor);
  root.style.setProperty('--secondary', primaryColor);
}
