"use client";

import { useState } from "react";
import { useCreateSeries } from "./useSerialApi";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { useRouter } from "next/navigation";

export function CreateSeriesButton() {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [title, setTitle] = useState("");
  const create = useCreateSeries();
  return (
    <>
      <Button size="sm" onClick={() => setOpen(true)}>New series</Button>
      {open && (
        <Card className="fixed inset-4 z-10 m-auto max-w-md">
          <CardHeader className="flex flex-row items-center justify-between">
            <CardTitle>New series</CardTitle>
            <Button variant="ghost" size="sm" onClick={() => setOpen(false)}>Cancel</Button>
          </CardHeader>
          <CardContent>
            <form
              onSubmit={(e) => {
                e.preventDefault();
                if (!title.trim()) return;
                create.mutate(
                  { title: title.trim() },
                  {
                    onSuccess: (result: unknown) => {
                      const data = result as { data?: { series?: { id: number } } };
                      const id = data?.data?.series?.id;
                      if (id) router.push("/serial/series/" + id);
                      setOpen(false);
                      setTitle("");
                    },
                  }
                );
              }}
              className="flex flex-col gap-2"
            >
              <Input
                placeholder="Series title"
                value={title}
                onChange={(e) => setTitle(e.target.value)}
              />
              <Button type="submit" disabled={create.isPending || !title.trim()}>
                {create.isPending ? "Creating…" : "Create"}
              </Button>
            </form>
          </CardContent>
        </Card>
      )}
    </>
  );
}
