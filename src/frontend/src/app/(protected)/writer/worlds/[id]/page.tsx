import { redirect } from "next/navigation";

export default async function WriterWorldRedirect({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  if (!id) return <p>Invalid world.</p>;
  redirect(`/world/${id}`);
}
