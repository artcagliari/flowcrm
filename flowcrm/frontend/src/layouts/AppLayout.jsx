import { Outlet } from 'react-router-dom';
import { useState } from 'react';
import Sidebar from '../components/layout/Sidebar';
import Navbar from '../components/layout/Navbar';
import useAuth from '../hooks/useAuth';

export default function AppLayout() {
  const [sidebar, setSidebar] = useState(false);
  const { user } = useAuth();
  return (
    <div className="min-h-screen lg:pl-[286px]">
      <Sidebar open={sidebar} onClose={() => setSidebar(false)} user={user} />
      <div className="min-w-0 p-3 lg:p-5">
        <Navbar onMenu={() => setSidebar(true)} />
        <main className="pt-6"><Outlet /></main>
      </div>
    </div>
  );
}
