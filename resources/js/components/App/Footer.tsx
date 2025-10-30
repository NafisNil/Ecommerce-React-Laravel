export default function Footer() {
  const year = new Date().getFullYear();
  return (
    <footer className="mt-10 border-t bg-base-100 text-base-content">
      <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div className="flex flex-col items-center justify-between gap-4 sm:flex-row">
          <p className="text-sm opacity-80">© {year} Reacrcom. All rights reserved.</p>
          <nav className="flex items-center gap-4 text-sm">
            <a className="link link-hover" href="#">About</a>
            <a className="link link-hover" href="#">Contact</a>
            <a className="link link-hover" href="#">Privacy</a>
            <a className="link link-hover" href="#">Terms</a>
          </nav>
        </div>
      </div>
    </footer>
  );
}
