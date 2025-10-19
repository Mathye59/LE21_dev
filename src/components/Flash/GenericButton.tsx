import React from 'react';
import { useNavigate } from 'react-router-dom';

type GenericButtonProps = {
  text: string;
  link?: string; // ex: "/Catalogue?search=python"
  bgColor?: string; // ex: "bg-cyanIT"
  textColor?: string; // ex: "text-white"
  borderColor?: string; // ex: "border-turquoise"
  borderSize?: string; // ex: "border" | "border-2" | "border-0"
  hoverBorderColor?: string; // ex: "hover:border-cyan-400"
  fontStyle?: string; // ex: "font-semibold text-base"
  className?: string; // ex: "px-6 py-2"
  onClick?: () => void; // optionnel: callback custom
};

const GenericButton: React.FC<GenericButtonProps> = ({
  text,
  link,
  bgColor = '',
  textColor = '',
  borderColor = '',
  borderSize = 'border',
  hoverBorderColor = '',
  fontStyle = 'font-semibold text-base',
  className = 'px-6 py-2',
  onClick,
}) => {
  const navigate = useNavigate();

  const handleClick = () => {
    onClick?.();
    if (link) navigate(link);
  };

  const classes = [
    borderSize,
    borderColor,
    hoverBorderColor,
    bgColor,
    textColor,
    fontStyle,
    className,
    'rounded-xl hover:scale-105 transition',
  ]
    .filter(Boolean)
    .join(' ');

  return (
    <button type="button" onClick={handleClick} className={classes}>
      {text}
    </button>
  );
};

export default GenericButton;
