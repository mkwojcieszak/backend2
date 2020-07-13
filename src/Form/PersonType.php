<?php

namespace App\Form;

use App\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login')
            ->add('l_name', TextType::class, [
                'label' => "Last Name"
            ])
            ->add('f_name', TextType::class, [
                'label' => "First Name"
            ])
            ->add('state', ChoiceType::class, [
                'label' => "State",
                'choices' => [
                    'Active' => 1,
                    'Banned' => 2,
                    'Deleted' => 3
                ]
            ])
            ->add('save', SubmitType::class, [
                'attr' => [ 'class' => 'btn btn-success']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
