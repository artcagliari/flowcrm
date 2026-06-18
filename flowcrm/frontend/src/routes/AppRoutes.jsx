import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import AppLayout from '../layouts/AppLayout';
import AdminLayout from '../layouts/AdminLayout';
import useAuth from '../hooks/useAuth';
import Login from '../pages/auth/Login';
import Dashboard from '../pages/Dashboard';
import Clients from '../pages/Clients';
import ClientDetails from '../pages/ClientDetails';
import Leads from '../pages/Leads';
import LeadDetails from '../pages/LeadDetails';
import Tasks from '../pages/Tasks';
import Appointments from '../pages/Appointments';
import Finance from '../pages/Finance';
import Documents from '../pages/Documents';
import Reports from '../pages/Reports';
import Settings from '../pages/Settings';
import Notifications from '../pages/Notifications';
import Users from '../pages/Users';
import Deals from '../pages/Deals';
import PipelineSettings from '../pages/PipelineSettings';
import Automations from '../pages/Automations';
import Integrations from '../pages/Integrations';
import NotFound from '../pages/NotFound';
import LoadingSpinner from '../components/ui/LoadingSpinner';
import AdminDashboard from '../pages/admin/AdminDashboard';
import AdminCompanies from '../pages/admin/AdminCompanies';
import AdminCompanyForm from '../pages/admin/AdminCompanyForm';
import AdminCompanyDetails from '../pages/admin/AdminCompanyDetails';
import AdminPlans from '../pages/admin/AdminPlans';
import AdminSettings from '../pages/admin/AdminSettings';

function LoadingGate({ children }) {
  const { loading } = useAuth();
  if (loading) return <main className="grid min-h-screen place-items-center"><LoadingSpinner /></main>;
  return children;
}

function RootRedirect() {
  const { authenticated, user } = useAuth();
  if (!authenticated) return <Navigate to="/login" replace />;
  return <Navigate to={user?.role === 'super_admin' ? '/admin' : '/dashboard'} replace />;
}

function AdminRoute() {
  const { authenticated, user } = useAuth();
  if (!authenticated) return <Navigate to="/login" replace />;
  if (user?.role !== 'super_admin') return <Navigate to="/dashboard" replace />;
  return <AdminLayout />;
}

function CompanyRoute() {
  const { authenticated, user } = useAuth();
  if (!authenticated) return <Navigate to="/login" replace />;
  if (user?.role === 'super_admin') return <Navigate to="/admin" replace />;
  return <AppLayout />;
}

export default function AppRoutes() {
  return (
    <BrowserRouter>
      <LoadingGate>
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route path="/" element={<RootRedirect />} />
          <Route element={<AdminRoute />}>
            <Route path="/admin" element={<AdminDashboard />} />
            <Route path="/admin/companies" element={<AdminCompanies />} />
            <Route path="/admin/companies/new" element={<AdminCompanyForm />} />
            <Route path="/admin/companies/:id" element={<AdminCompanyDetails />} />
            <Route path="/admin/companies/:id/edit" element={<AdminCompanyForm />} />
            <Route path="/admin/plans" element={<AdminPlans />} />
            <Route path="/admin/settings" element={<AdminSettings />} />
          </Route>
          <Route element={<CompanyRoute />}>
            <Route path="/dashboard" element={<Dashboard />} />
            <Route path="/clients" element={<Clients />} />
            <Route path="/clients/:id" element={<ClientDetails />} />
            <Route path="/leads" element={<Leads />} />
            <Route path="/leads/:id" element={<LeadDetails />} />
            <Route path="/deals" element={<Deals />} />
            <Route path="/pipeline" element={<PipelineSettings />} />
            <Route path="/automations" element={<Automations />} />
            <Route path="/integrations" element={<Integrations />} />
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
      </LoadingGate>
    </BrowserRouter>
  );
}
