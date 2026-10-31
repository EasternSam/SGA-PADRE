import * as React from "react";
import { Button as ShadcnButton } from "@/components/ui/button";

export const Button = React.forwardRef(function Button(
  { className, variant = "secondary", size = "sm", children, ...props },
  ref
) {
  return (
    <ShadcnButton
      ref={ref}
      variant={variant === "secondary" ? "ghost" : variant}
      size={size}
      className={className}
      {...props}
    >
      {children}
    </ShadcnButton>
  );
});

Button.displayName = "RegistryButton";
export default Button;
