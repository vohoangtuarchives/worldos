"use client";

import { Button } from "@/components/ui/button";
import { CreateSagaDialog } from "./CreateSagaDialog";
import { Sparkles } from "lucide-react";

export function CreateSagaButton() {
  return (
    <CreateSagaDialog>
      <Button size="sm" className="gap-2 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 border-0">
        <Sparkles className="w-4 h-4" />
        Create Saga from Active
      </Button>
    </CreateSagaDialog>
  );
}
