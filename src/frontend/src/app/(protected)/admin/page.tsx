import { AdminDashboard } from "@/features/admin/AdminDashboard";

export default function AdminPage() {
  return (
    <div className="p-6">
      <h1 className="mb-6 text-2xl font-semibold">Admin</h1>
      <AdminDashboard />
    </div>
  );
}
