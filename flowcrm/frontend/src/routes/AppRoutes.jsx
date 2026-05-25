import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import AppLayout from '../layouts/AppLayout';
import useAuth from '../hooks/useAuth';
import Login from '../pages/auth/Login';
import Register from '../pages/auth/Register';
import Dashboard from '../pages/Dashboard';
import Clients from '../pages/Clients';
import ClientDetails from '../pages/ClientDetails';
import Leads from '../pages/Leads';
import LeadDetails from '../pages/LeadDetails';
import Kanban from '../pages/Kanban';
import Tasks from '../pages/Tasks';
import Appointments from '../pages/Appointments';
import Finance from '../pages/Finance';
import Documents from '../pages/Documents';
import Reports from '../pages/Reports';
import Settings from '../pages/Settings';
import Notifications from '../pages/Notifications';
import Users from '../pages/Users';
import NotFound from '../pages/NotFound';
import LoadingSpinner from '../components/ui/LoadingSpinner';

function ProtectedRoute() {
  const { authenticated, loading } = useAuth();
  if (loading) return <main className="grid min-h-screen place-items-center"><LoadingSpinner /></main>;
  return authenticated ? <AppLayout /> : <Navigate to="/login" replace />;
}

export default function AppRoutes() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route element={<ProtectedRoute />}>
          <Route index element={<Dashboard />} />
          <Route path="/clients" element={<Clients />} />
          <Route path="/clients/:id" element={<ClientDetails />} />
          <Route path="/leads" element={<Leads />} />
          <Route path="/leads/:id" element={<LeadDetails />} />
          <Route path="/kanban" element={<Kanban />} />
          <Route path="/tasks" element={<Tasks />} />
          <Route path="/appointments" element={<Appointments />} />
          <Route path="/finance" element={<Finance />} />
          <Route path="/documents" element={<Documents />} />
          <Route path="/reports" element={<Reports />} />
          <Route path="/settings" element={<Settings />} />
          <Route path="/notifications" element={<Notifications />} />
          <Route path="/users" element={<Users />} />
        </Route>
        <Route path="*" element={<NotFound />} />
      </Routes>
    </BrowserRouter>
  );
}
